<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Department;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Statuslabel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DeployedAssetsImportSeeder
 *
 * Imports deployed assets from CSV file with the following columns:
 * Asset Tag, Model, Model No., Category, Manufacturer, Serial, Order Number,
 * Location, Checked Out, Type, Username, Department, Status, Notes, Device Status, ASSET OWNER
 *
 * Features:
 * - Creates/updates manufacturers, models, categories, locations, departments
 * - Handles asset assignment to users or locations
 * - Properly maps status labels
 * - Validates and handles edge cases (missing data, duplicates, etc.)
 * - Supports transactions for data integrity
 */
class DeployedAssetsImportSeeder extends Seeder
{
    private $admin;

    private $categoryMap = [];

    private $manufacturerMap = [];

    private $locationMap = [];

    private $departmentMap = [];

    private $userMap = [];

    private $modelMap = [];

    private $statusMap = [];

    private $errors = [];

    private $created = 0;

    private $updated = 0;

    private $failed = 0;

    public function run()
    {
        $csvFile = storage_path('app/deployed_assets.csv');

        if (!file_exists($csvFile)) {
            $this->command->warn("CSV file not found at: {$csvFile}");
            return;
        }

        $this->initializeAdmin();
        $this->cacheRelatedData();
        $this->importAssets($csvFile);
        $this->reportResults();
    }

    /**
     * Initialize admin user for created_by field
     */
    private function initializeAdmin(): void
    {
        $this->admin = User::where('permissions->superuser', '1')
            ->first() ?? User::factory()->firstAdmin()->create();

        Log::info("Using admin user: {$this->admin->username}");
    }

    /**
     * Cache all related entities for faster lookups
     */
    private function cacheRelatedData(): void
    {
        // Cache all manufacturers
        Manufacturer::all()->each(fn ($m) => $this->manufacturerMap[strtolower($m->name)] = $m->id);

        // Cache all categories
        Category::all()->each(fn ($c) => $this->categoryMap[strtolower($c->name)] = $c->id);

        // Cache all locations
        Location::all()->each(fn ($l) => $this->locationMap[strtolower($l->name)] = $l->id);

        // Cache all departments
        Department::all()->each(fn ($d) => $this->departmentMap[strtolower($d->name)] = $d->id);

        // Cache all users
        User::all()->each(fn ($u) => $this->userMap[strtolower($u->username)] = $u->id);

        // Cache status labels
        Statuslabel::all()->each(fn ($s) => $this->statusMap[strtolower($s->name)] = $s->id);
    }

    /**
     * Import assets from CSV file
     */
    private function importAssets(string $csvFile): void
    {
        if (!($file = fopen($csvFile, 'r'))) {
            $this->command->error("Cannot open CSV file: {$csvFile}");
            return;
        }

        $headers = fgetcsv($file);
        if (!$headers) {
            $this->command->error("CSV file is empty");
            fclose($file);
            return;
        }

        $columnMap = $this->mapColumns($headers);
        $rowNumber = 1;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file)) !== false) {
                $rowNumber++;

                if (empty(array_filter($row))) {
                    continue; // Skip empty rows
                }

                $data = array_combine($headers, $row);
                $this->importAsset($data, $rowNumber);

                // Commit in batches to avoid memory issues
                if ($rowNumber % 100 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                    gc_collect_cycles();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Import failed: {$e->getMessage()}");
            $this->command->error("Import failed: {$e->getMessage()}");
        }

        fclose($file);
    }

    /**
     * Map CSV columns to expected column names
     */
    private function mapColumns(array $headers): array
    {
        $mapping = [];
        $expectedColumns = [
            'Asset Tag',
            'Model',
            'Model No.',
            'Category',
            'Manufacturer',
            'Serial',
            'Order Number',
            'Location',
            'Checked Out',
            'Type',
            'Username',
            'Department',
            'Status',
            'Notes',
            'Device Status',
            'ASSET OWNER',
        ];

        foreach ($expectedColumns as $expected) {
            $index = array_search($expected, $headers);
            if ($index !== false) {
                $mapping[$expected] = $index;
            }
        }

        return $mapping;
    }

    /**
     * Import a single asset
     */
    private function importAsset(array $data, int $rowNumber): void
    {
        try {
            $assetTag = trim($data['Asset Tag'] ?? '');
            if (empty($assetTag)) {
                $this->recordError($rowNumber, 'Asset Tag is required');
                return;
            }

            // Get or create manufacturer
            $manufacturerId = $this->getOrCreateManufacturer($data['Manufacturer'] ?? '');

            // Get or create category
            $categoryId = $this->getOrCreateCategory($data['Category'] ?? '');

            // Get or create asset model
            $modelId = $this->getOrCreateAssetModel(
                $data['Model'] ?? '',
                $data['Model No.'] ?? '',
                $manufacturerId,
                $categoryId
            );

            // Get or create location
            $locationId = $this->getOrCreateLocation($data['Location'] ?? '');

            // Get status label
            $statusId = $this->getStatusId($data['Status'] ?? '', $data['Device Status'] ?? '');

            // Get or create department
            $departmentId = $this->getOrCreateDepartment($data['Department'] ?? '', $locationId);

            // Determine assignment
            $assignmentData = $this->resolveAssignment(
                $data['Username'] ?? '',
                $data['Checked Out'] ?? '',
                $data['ASSET OWNER'] ?? '',
                $locationId
            );

            // Build asset data
            $assetData = [
                'asset_tag' => $assetTag,
                'model_id' => $modelId,
                'status_id' => $statusId,
                'serial' => !empty($data['Serial']) ? trim($data['Serial']) : null,
                'order_number' => !empty($data['Order Number']) ? trim($data['Order Number']) : null,
                'location_id' => $assignmentData['location_id'],
                'rtd_location_id' => $locationId,
                'notes' => !empty($data['Notes']) ? trim($data['Notes']) : null,
                'created_by' => $this->admin->id,
                'assigned_to' => $assignmentData['assigned_to'],
                'assigned_type' => $assignmentData['assigned_type'],
            ];

            // Create or update asset
            $asset = Asset::firstOrNew(['asset_tag' => $assetTag]);
            if (!$asset->exists) {
                $asset->fill($assetData)->save();
                $this->created++;
                Log::info("Created asset: {$assetTag}");
            } else {
                $asset->update($assetData);
                $this->updated++;
                Log::info("Updated asset: {$assetTag}");
            }
        } catch (\Exception $e) {
            $this->recordError($rowNumber, $e->getMessage());
        }
    }

    /**
     * Get or create manufacturer
     */
    private function getOrCreateManufacturer(?string $name): ?int
    {
        if (empty($name)) {
            return null;
        }

        $nameKey = strtolower(trim($name));

        if (isset($this->manufacturerMap[$nameKey])) {
            return $this->manufacturerMap[$nameKey];
        }

        $manufacturer = Manufacturer::firstOrCreate(
            ['name' => trim($name)],
            ['created_by' => $this->admin->id]
        );

        $this->manufacturerMap[$nameKey] = $manufacturer->id;

        return $manufacturer->id;
    }

    /**
     * Get or create category
     */
    private function getOrCreateCategory(?string $name): int
    {
        if (empty($name)) {
            $name = 'Uncategorized';
        }

        $nameKey = strtolower(trim($name));

        if (isset($this->categoryMap[$nameKey])) {
            return $this->categoryMap[$nameKey];
        }

        // Map common category names to proper Snipe-IT categories
        $categoryType = $this->determineCategoryType($name);

        $category = Category::firstOrCreate(
            ['name' => trim($name), 'category_type' => $categoryType],
            [
                'category_type' => $categoryType,
                'created_by' => $this->admin->id,
            ]
        );

        $this->categoryMap[$nameKey] = $category->id;

        return $category->id;
    }

    /**
     * Determine category type based on name
     */
    private function determineCategoryType(string $name): string
    {
        $name = strtolower($name);

        if (Str::contains($name, ['laptop', 'desktop', 'cpu', 'workstation', 'server', 'computer'])) {
            return 'asset';
        }
        if (Str::contains($name, ['monitor', 'display'])) {
            return 'asset';
        }
        if (Str::contains($name, ['keyboard', 'mouse', 'printer'])) {
            return 'accessory';
        }
        if (Str::contains($name, ['cctv', 'camera', 'access'])) {
            return 'accessory';
        }

        return 'asset'; // Default to asset
    }

    /**
     * Get or create asset model
     */
    private function getOrCreateAssetModel(
        ?string $modelName,
        ?string $modelNumber,
        ?int $manufacturerId,
        int $categoryId
    ): int {
        $modelName = trim($modelName ?? 'Unknown Model');
        $modelNumber = !empty($modelNumber) ? trim($modelNumber) : null;

        // Create unique key for model caching
        $modelKey = strtolower("{$modelName}_{$modelNumber}_{$manufacturerId}");

        if (isset($this->modelMap[$modelKey])) {
            return $this->modelMap[$modelKey];
        }

        $model = AssetModel::firstOrCreate(
            [
                'name' => $modelName,
                'model_number' => $modelNumber,
                'manufacturer_id' => $manufacturerId,
            ],
            [
                'category_id' => $categoryId,
                'manufacturer_id' => $manufacturerId,
                'created_by' => $this->admin->id,
            ]
        );

        $this->modelMap[$modelKey] = $model->id;

        return $model->id;
    }

    /**
     * Get or create location
     */
    private function getOrCreateLocation(?string $name): int
    {
        if (empty($name)) {
            $name = 'Default Location';
        }

        $nameKey = strtolower(trim($name));

        if (isset($this->locationMap[$nameKey])) {
            return $this->locationMap[$nameKey];
        }

        $location = Location::firstOrCreate(
            ['name' => trim($name)],
            ['created_by' => $this->admin->id]
        );

        $this->locationMap[$nameKey] = $location->id;

        return $location->id;
    }

    /**
     * Get or create department
     */
    private function getOrCreateDepartment(?string $name, int $locationId): ?int
    {
        if (empty($name)) {
            return null;
        }

        $nameKey = strtolower(trim($name));

        if (isset($this->departmentMap[$nameKey])) {
            return $this->departmentMap[$nameKey];
        }

        $department = Department::firstOrCreate(
            ['name' => trim($name)],
            [
                'location_id' => $locationId,
                'created_by' => $this->admin->id,
            ]
        );

        $this->departmentMap[$nameKey] = $department->id;

        return $department->id;
    }

    /**
     * Get status label ID
     */
    private function getStatusId(?string $status, ?string $deviceStatus): int
    {
        $status = strtolower(trim($status ?? ''));
        $deviceStatus = strtolower(trim($deviceStatus ?? ''));

        // Determine if deployed or deployable
        $isDeployed = Str::contains($status, ['deployed', 'checked out']) ||
                      Str::contains($deviceStatus, ['deployed']);
        $isDeployable = Str::contains($status, ['deployable', 'ready']) ||
                        Str::contains($deviceStatus, ['deployable']);

        // Get appropriate status
        if ($isDeployed || (empty($status) && empty($deviceStatus))) {
            // Default to "Ready to Deploy" for deployed/unknown
            $statusName = 'Ready to Deploy';
        } elseif ($isDeployable) {
            $statusName = 'Ready to Deploy';
        } else {
            $statusName = trim($status) ?: 'Ready to Deploy';
        }

        // Look up status in cache, fallback to database
        $statusKey = strtolower($statusName);
        if (isset($this->statusMap[$statusKey])) {
            return $this->statusMap[$statusKey];
        }

        // If not found, use Ready to Deploy
        $defaultStatus = Statuslabel::where('deployable', 1)
            ->where('archived', 0)
            ->first();

        if ($defaultStatus) {
            $this->statusMap[$statusKey] = $defaultStatus->id;
            return $defaultStatus->id;
        }

        // Fallback: create Ready to Deploy status if it doesn't exist
        $newStatus = Statuslabel::create([
            'name' => $statusName,
            'deployable' => 1,
            'pending' => 0,
            'archived' => 0,
            'created_by' => $this->admin->id,
        ]);

        $this->statusMap[$statusKey] = $newStatus->id;

        return $newStatus->id;
    }

    /**
     * Resolve asset assignment (user or location)
     */
    private function resolveAssignment(
        ?string $username,
        ?string $checkedOut,
        ?string $assetOwner,
        int $locationId
    ): array {
        $username = !empty($username) ? trim($username) : null;
        $checkedOut = strtolower(trim($checkedOut ?? ''));
        $assetOwner = !empty($assetOwner) ? trim($assetOwner) : null;

        // Determine who to check out to
        $userToAssign = $username ?? $assetOwner;

        if (!empty($userToAssign)) {
            $userKey = strtolower($userToAssign);

            if (!isset($this->userMap[$userKey])) {
                // Try to find or create user
                $user = $this->findOrCreateUser($userToAssign);
                if ($user) {
                    $this->userMap[$userKey] = $user->id;
                }
            }

            if (isset($this->userMap[$userKey])) {
                return [
                    'assigned_to' => $this->userMap[$userKey],
                    'assigned_type' => User::class,
                    'location_id' => $locationId,
                ];
            }
        }

        // If no user assignment, check if it should be assigned to location
        if (Str::contains($checkedOut, ['yes', 'true', '1'])) {
            return [
                'assigned_to' => $locationId,
                'assigned_type' => Location::class,
                'location_id' => $locationId,
            ];
        }

        // Default: unassigned, at location
        return [
            'assigned_to' => null,
            'assigned_type' => null,
            'location_id' => $locationId,
        ];
    }

    /**
     * Find or create user by username
     */
    private function findOrCreateUser(string $username): ?User
    {
        $user = User::where('username', $username)->first();

        if ($user) {
            return $user;
        }

        // Try to parse name from username (e.g., "john.doe" -> "John Doe")
        $parts = explode('.', $username);
        $firstName = ucfirst($parts[0] ?? '');
        $lastName = ucfirst($parts[1] ?? '') ?: '';

        // Create user
        try {
            $user = User::create([
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'display_name' => "{$firstName} {$lastName}",
                'email' => "{$username}@cadfem.in",
                'activated' => 1,
                'created_by' => $this->admin->id,
            ]);

            Log::info("Created user: {$username}");

            return $user;
        } catch (\Exception $e) {
            Log::warning("Failed to create user {$username}: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Record an import error
     */
    private function recordError(int $rowNumber, string $message): void
    {
        $this->errors[] = "Row {$rowNumber}: {$message}";
        $this->failed++;
        Log::warning("Row {$rowNumber}: {$message}");
    }

    /**
     * Report import results
     */
    private function reportResults(): void
    {
        $this->command->line('');
        $this->command->info('=== IMPORT RESULTS ===');
        $this->command->info("Assets Created: {$this->created}");
        $this->command->info("Assets Updated: {$this->updated}");
        $this->command->error("Failed Rows: {$this->failed}");

        if (!empty($this->errors)) {
            $this->command->warn("\n=== ERRORS ===");
            foreach (array_slice($this->errors, 0, 20) as $error) {
                $this->command->warn($error);
            }

            if (count($this->errors) > 20) {
                $this->command->warn("... and " . (count($this->errors) - 20) . " more errors");
            }
        }

        $this->command->line('');
        $this->command->info('Import complete!');
    }
}
