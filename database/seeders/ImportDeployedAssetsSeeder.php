<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\User;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Department;
use App\Models\Statuslabel;
use Illuminate\Support\Facades\Log;

class ImportDeployedAssetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = storage_path('app/deployed_assets_2026.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: $csvFile");
            return;
        }

        $this->command->info("Importing assets from: $csvFile");

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);
        $count = 0;
        $errors = 0;

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($header, $row);

                // Skip empty rows
                if (empty($data['Asset Tag'])) {
                    continue;
                }

                $count++;

                // Find or create manufacturer
                $manufacturer = Manufacturer::firstOrCreate(
                    ['name' => $data['Manufacturer'] ?? 'Unknown'],
                    ['url' => '', 'image' => '']
                );

                // Find or create category
                $category = Category::firstOrCreate(
                    ['name' => $data['Category'] ?? 'Other'],
                    ['category_type' => $this->mapCategoryType($data['Category'] ?? 'Other')]
                );

                // Find or create asset model
                $model = AssetModel::firstOrCreate(
                    [
                        'name' => $data['Model'] ?? 'Unknown',
                        'manufacturer_id' => $manufacturer->id
                    ],
                    ['category_id' => $category->id, 'model_number' => $data['Model No.'] ?? '']
                );

                // Find or create location
                $location = Location::firstOrCreate(
                    ['name' => $data['Location'] ?? 'Hyderabad'],
                    ['address' => '', 'city' => $data['Location'] ?? 'Hyderabad', 'country' => 'India']
                );

                // Find or create department (if provided)
                $department = null;
                if (!empty($data['Department'])) {
                    $department = Department::firstOrCreate(['name' => $data['Department']]);
                }

                // Get status
                $status = Statuslabel::where('name', 'Deployed')->first()
                    ?? Statuslabel::where('deployable', 1)->first()
                    ?? Statuslabel::first();

                // Create asset
                $asset = Asset::create([
                    'name' => $data['Model'] ?? 'Asset ' . $data['Asset Tag'],
                    'asset_tag' => $data['Asset Tag'],
                    'serial' => $data['Serial'] ?? '',
                    'model_id' => $model->id,
                    'manufacturer_id' => $manufacturer->id,
                    'category_id' => $category->id,
                    'location_id' => $location->id,
                    'status_id' => $status->id,
                    'notes' => $data['Notes'] ?? '',
                    'purchase_date' => null,
                    'purchase_cost' => 0,
                ]);

                // Assign to user if provided
                if (!empty($data['Username'])) {
                    $user = User::where('username', $data['Username'])->first();
                    if ($user) {
                        $asset->assigned_to = $user->id;
                        $asset->assigned_type = 'App\\Models\\User';
                        $asset->save();
                    }
                }

                if ($count % 10 == 0) {
                    $this->command->info("Imported $count assets...");
                }

            } catch (\Exception $e) {
                $errors++;
                Log::error('Asset import error: ' . $e->getMessage(), ['row' => $count]);
                $this->command->error("Error on row $count: " . $e->getMessage());
            }
        }

        fclose($handle);

        $this->command->info("✓ Import complete!");
        $this->command->line("  Total imported: $count");
        $this->command->line("  Errors: $errors");
    }

    private function mapCategoryType($category): string
    {
        $typeMap = [
            'Laptop' => 'laptop',
            'Desktop' => 'desktop',
            'Monitor' => 'monitor',
            'CPU' => 'desktop',
            'Server' => 'server',
            'Workstation' => 'desktop',
            'Peripheral' => 'peripheral',
            'Software' => 'software',
            'License' => 'license',
            'CCTV' => 'component',
            'Access Points' => 'component',
            'Access Control' => 'component',
            'Projector' => 'peripheral',
        ];

        return $typeMap[trim($category)] ?? 'component';
    }
}
