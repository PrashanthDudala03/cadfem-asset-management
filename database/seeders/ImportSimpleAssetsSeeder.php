<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportSimpleAssetsSeeder extends Seeder
{
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

                if (empty($data['Asset Tag'])) {
                    continue;
                }

                $count++;

                // Get status ID (default to 1)
                $statusId = DB::table('status_labels')
                    ->where('name', 'Deployed')
                    ->value('id') ?? 1;

                // Get or create category
                $categoryId = DB::table('categories')
                    ->where('name', $data['Category'] ?? 'Other')
                    ->value('id');

                if (!$categoryId) {
                    $categoryId = DB::table('categories')->insertGetId([
                        'name' => $data['Category'] ?? 'Other',
                        'category_type' => 'asset',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Get or create manufacturer
                $manufacturerId = DB::table('manufacturers')
                    ->where('name', $data['Manufacturer'] ?? 'Unknown')
                    ->value('id');

                if (!$manufacturerId) {
                    $manufacturerId = DB::table('manufacturers')->insertGetId([
                        'name' => $data['Manufacturer'] ?? 'Unknown',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Get or create model
                $modelId = DB::table('models')
                    ->where('name', $data['Model'] ?? 'Unknown')
                    ->value('id');

                if (!$modelId) {
                    $modelId = DB::table('models')->insertGetId([
                        'name' => $data['Model'] ?? 'Unknown',
                        'manufacturer_id' => $manufacturerId,
                        'category_id' => $categoryId,
                        'model_number' => $data['Model No.'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Get or create location
                $locationId = DB::table('locations')
                    ->where('name', $data['Location'] ?? 'Hyderabad')
                    ->value('id');

                if (!$locationId) {
                    $locationId = DB::table('locations')->insertGetId([
                        'name' => $data['Location'] ?? 'Hyderabad',
                        'city' => $data['Location'] ?? 'Hyderabad',
                        'country' => 'India',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Insert asset
                DB::table('assets')->insert([
                    'name' => $data['Model'] ?? 'Asset ' . $data['Asset Tag'],
                    'asset_tag' => $data['Asset Tag'],
                    'serial' => $data['Serial'] ?? '',
                    'model_id' => $modelId,
                    'location_id' => $locationId,
                    'status_id' => $statusId,
                    'notes' => $data['Notes'] ?? '',
                    'purchase_cost' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($count % 10 == 0) {
                    $this->command->info("Imported $count assets...");
                }

            } catch (\Exception $e) {
                $errors++;
                $this->command->error("Error on row $count: " . $e->getMessage());
            }
        }

        fclose($handle);

        $this->command->info("✓ Import complete!");
        $this->command->line("  Total imported: $count");
        $this->command->line("  Errors: $errors");
    }
}
