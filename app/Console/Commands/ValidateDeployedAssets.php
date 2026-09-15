<?php

namespace App\Console\Commands;

use App\Services\DeployedAssetsValidator;
use Illuminate\Console\Command;

/**
 * ValidateDeployedAssets Command
 *
 * Validates CSV file format and content before importing
 *
 * Usage:
 *   php artisan validate:deployed-assets
 *   php artisan validate:deployed-assets --file="path/to/file.csv"
 *   php artisan validate:deployed-assets --file="file.csv" --summary
 */
class ValidateDeployedAssets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'validate:deployed-assets {--file= : Path to CSV file} {--summary : Show only summary} {--errors-only : Show only errors}';

    /**
     * The description of the console command.
     */
    protected $description = 'Validate deployed assets CSV file before importing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Deployed Assets CSV Validator');
        $this->info('==============================');
        $this->line('');

        // Get CSV file path
        $file = $this->option('file') ?? $this->askForFile();

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info("Validating: {$file}");
        $this->line('');

        // Run validation
        $validator = new DeployedAssetsValidator($file);
        $isValid = $validator->validate();

        // Display results
        if ($this->option('summary')) {
            $this->displaySummary($validator);
        } else {
            $this->displayFullReport($validator);
        }

        // Return appropriate exit code
        return $isValid ? 0 : 1;
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
     * Display full validation report
     */
    private function displayFullReport(DeployedAssetsValidator $validator): void
    {
        $stats = $validator->getStats();
        $errors = $validator->getErrors();
        $warnings = $validator->getWarnings();

        // Statistics
        $this->info('STATISTICS:');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Data Rows', $stats['total_data_rows'] ?? 0],
                ['Valid Rows', $stats['valid_rows'] ?? 0],
                ['Empty Rows', $stats['empty_rows'] ?? 0],
                ['Unique Asset Tags', $stats['asset_tags'] ?? 0],
                ['Unique Serials', $stats['serials'] ?? 0],
            ]
        );

        $this->line('');

        // Errors
        if (!empty($errors)) {
            $this->error("ERRORS (" . count($errors) . "):");
            foreach (array_slice($errors, 0, 20) as $error) {
                $this->error("  ✗ {$error}");
            }
            if (count($errors) > 20) {
                $this->error("  ... and " . (count($errors) - 20) . " more errors");
            }
            $this->line('');
        }

        // Warnings (only if no errors)
        if (!$this->option('errors-only') && !empty($warnings) && empty($errors)) {
            $this->warn("WARNINGS (" . count($warnings) . "):");
            foreach (array_slice($warnings, 0, 20) as $warning) {
                $this->warn("  ⚠ {$warning}");
            }
            if (count($warnings) > 20) {
                $this->warn("  ... and " . (count($warnings) - 20) . " more warnings");
            }
            $this->line('');
        }

        // Validation result
        if (empty($errors)) {
            $this->info('✓ VALIDATION PASSED - File is ready for import');
            if (!empty($warnings)) {
                $this->warn("  {$this->pluralize(count($warnings), 'warning')} found - review before importing");
            }
        } else {
            $this->error('✗ VALIDATION FAILED - Fix errors before importing');
        }

        $this->line('');
    }

    /**
     * Display summary only
     */
    private function displaySummary(DeployedAssetsValidator $validator): void
    {
        $stats = $validator->getStats();
        $errors = $validator->getErrors();
        $warnings = $validator->getWarnings();

        // Summary table
        $this->table(
            ['Item', 'Count'],
            [
                ['Data Rows', $stats['total_data_rows'] ?? 0],
                ['Valid Rows', $stats['valid_rows'] ?? 0],
                ['Errors', count($errors)],
                ['Warnings', count($warnings)],
            ]
        );

        $this->line('');

        // Validation status
        if (empty($errors)) {
            $this->info('✓ Status: READY TO IMPORT');
        } else {
            $this->error('✗ Status: NEEDS FIXES');
            $this->error("  {$this->pluralize(count($errors), 'error')} found");
        }

        $this->line('');
    }

    /**
     * Pluralize word based on count
     */
    private function pluralize(int $count, string $word): string
    {
        return $count === 1 ? "1 $word" : "$count ${word}s";
    }
}
