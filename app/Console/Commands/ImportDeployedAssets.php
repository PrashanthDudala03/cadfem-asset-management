<?php

namespace App\Console\Commands;

use Database\Seeders\DeployedAssetsImportSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ImportDeployedAssets Command
 *
 * Usage:
 *   php artisan import:deployed-assets
 *   php artisan import:deployed-assets --file="path/to/file.csv"
 *   php artisan import:deployed-assets --dry-run
 */
class ImportDeployedAssets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'import:deployed-assets {--file= : Path to CSV file} {--dry-run : Run without saving to database}';

    /**
     * The description of the console command.
     */
    protected $description = 'Import deployed assets from CSV file into the asset management system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Deployed Assets Import Tool');
        $this->info('============================');
        $this->line('');

        // Get CSV file path
        $file = $this->option('file') ?? $this->askForFile();

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("CSV File: {$file}");

        // Show file preview
        if ($this->confirm('Preview first 5 rows?', true)) {
            $this->previewFile($file, 5);
        }

        // Confirm before proceeding
        if (!$this->confirm('Proceed with import?', true)) {
            $this->warn('Import cancelled.');
            return 0;
        }

        // Handle dry-run
        if ($this->option('dry-run')) {
            $this->warn('Running in DRY-RUN mode (no database changes will be made)');
            DB::beginTransaction();
        }

        try {
            // Run the seeder
            $this->call('db:seed', ['--class' => DeployedAssetsImportSeeder::class]);

            if ($this->option('dry-run')) {
                DB::rollBack();
                $this->warn('DRY-RUN: All changes have been rolled back.');
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Ask user for CSV file path
     */
    private function askForFile(): string
    {
        $defaultPath = storage_path('app/deployed_assets.csv');

        $path = $this->ask(
            "Enter CSV file path",
            $defaultPath
        );

        return $path ?: $defaultPath;
    }

    /**
     * Preview CSV file
     */
    private function previewFile(string $file, int $lines): void
    {
        if (!($handle = fopen($file, 'r'))) {
            $this->error("Cannot open file: {$file}");
            return;
        }

        $this->line('');
        $this->info('File Preview (first ' . $lines . ' rows):');
        $this->line('-' . str_repeat('-', 200) . '-');

        $count = 0;
        while (($row = fgetcsv($handle)) !== false && $count < $lines) {
            if ($count === 0) {
                // Header row
                $this->table(
                    $row,
                    []
                );
            } else {
                // Data rows
                $this->line('Row ' . $count . ': ' . implode(' | ', array_map(fn ($v) => substr($v, 0, 20), $row)));
            }
            $count++;
        }

        $this->line('-' . str_repeat('-', 200) . '-');
        $this->line('');

        fclose($handle);
    }
}
