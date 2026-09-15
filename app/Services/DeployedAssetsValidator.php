<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * DeployedAssetsValidator
 *
 * Validates CSV file format and content before importing
 * Can be used to identify issues early and provide detailed error reports
 */
class DeployedAssetsValidator
{
    private $filePath;

    private $errors = [];

    private $warnings = [];

    private $stats = [];

    private $requiredColumns = [
        'Asset Tag',
        'Model',
        'Category',
        'Manufacturer',
    ];

    private $allColumns = [
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

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->initializeStats();
    }

    /**
     * Validate the entire CSV file
     */
    public function validate(): bool
    {
        // Check file exists
        if (!file_exists($this->filePath)) {
            $this->addError("File not found: {$this->filePath}");
            return false;
        }

        // Check file is readable
        if (!is_readable($this->filePath)) {
            $this->addError("File is not readable: {$this->filePath}");
            return false;
        }

        // Validate structure and content
        if (!$this->validateStructure()) {
            return false;
        }

        if (!$this->validateContent()) {
            return false;
        }

        return true;
    }

    /**
     * Validate CSV structure (headers and format)
     */
    private function validateStructure(): bool
    {
        if (!($file = fopen($this->filePath, 'r'))) {
            $this->addError("Cannot open file: {$this->filePath}");
            return false;
        }

        $headers = fgetcsv($file);
        if (!$headers) {
            $this->addError("CSV file is empty or cannot read headers");
            fclose($file);
            return false;
        }

        // Check for required columns
        $missingColumns = array_diff($this->requiredColumns, $headers);
        if (!empty($missingColumns)) {
            $this->addError("Missing required columns: " . implode(', ', $missingColumns));
            fclose($file);
            return false;
        }

        // Check for unexpected columns
        $unexpectedColumns = array_diff($headers, $this->allColumns);
        if (!empty($unexpectedColumns)) {
            $this->addWarning("Unexpected columns will be ignored: " . implode(', ', $unexpectedColumns));
        }

        // Check column count consistency
        $headerCount = count($headers);
        $maxColumns = count($headers);

        fclose($file);
        return true;
    }

    /**
     * Validate CSV content (data integrity and business rules)
     */
    private function validateContent(): bool
    {
        if (!($file = fopen($this->filePath, 'r'))) {
            return false;
        }

        $headers = fgetcsv($file);
        $columnMap = array_flip($headers);

        $rowNumber = 1;
        $assetTags = [];
        $serials = [];
        $emptyRows = 0;

        while (($row = fgetcsv($file)) !== false) {
            $rowNumber++;

            if (empty(array_filter($row))) {
                $emptyRows++;
                continue;
            }

            $data = array_combine($headers, $row);

            // Validate each row
            $this->validateRow($data, $rowNumber, $assetTags, $serials);
        }

        $this->stats['total_data_rows'] = $rowNumber - 2; // Subtract header and count start
        $this->stats['empty_rows'] = $emptyRows;
        $this->stats['valid_rows'] = $this->stats['total_data_rows'] - $emptyRows;
        $this->stats['asset_tags'] = count($assetTags);
        $this->stats['serials'] = count($serials);

        fclose($file);
        return empty($this->errors);
    }

    /**
     * Validate a single row
     */
    private function validateRow(array $data, int $rowNumber, array &$assetTags, array &$serials): void
    {
        // Check Asset Tag (required)
        $assetTag = trim($data['Asset Tag'] ?? '');
        if (empty($assetTag)) {
            $this->addError("Row {$rowNumber}: Asset Tag is required");
            return;
        }

        // Check for duplicate Asset Tag
        if (in_array($assetTag, $assetTags)) {
            $this->addError("Row {$rowNumber}: Duplicate Asset Tag '{$assetTag}'");
        } else {
            $assetTags[] = $assetTag;
        }

        // Validate Asset Tag format (should be alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $assetTag)) {
            $this->addWarning("Row {$rowNumber}: Asset Tag '{$assetTag}' contains non-standard characters");
        }

        // Check Model (required)
        $model = trim($data['Model'] ?? '');
        if (empty($model)) {
            $this->addError("Row {$rowNumber}: Model is required");
        }

        // Check Category (required)
        $category = trim($data['Category'] ?? '');
        if (empty($category)) {
            $this->addError("Row {$rowNumber}: Category is required");
        }

        // Check Manufacturer (required)
        $manufacturer = trim($data['Manufacturer'] ?? '');
        if (empty($manufacturer)) {
            $this->addError("Row {$rowNumber}: Manufacturer is required");
        }

        // Validate Serial (must be unique if provided)
        $serial = trim($data['Serial'] ?? '');
        if (!empty($serial)) {
            if (in_array($serial, $serials)) {
                $this->addError("Row {$rowNumber}: Duplicate Serial Number '{$serial}'");
            } else {
                $serials[] = $serial;
            }
        }

        // Validate Checked Out field
        $checkedOut = strtolower(trim($data['Checked Out'] ?? ''));
        if (!empty($checkedOut) && !in_array($checkedOut, ['yes', 'no', 'true', 'false', '1', '0'])) {
            $this->addWarning("Row {$rowNumber}: Checked Out value '{$checkedOut}' is not standard (use Yes/No or True/False)");
        }

        // Warn if username but no location
        $username = trim($data['Username'] ?? '');
        $location = trim($data['Location'] ?? '');
        if (!empty($username) && empty($location)) {
            $this->addWarning("Row {$rowNumber}: User '{$username}' assigned but no Location specified");
        }

        // Validate Status if provided
        $status = trim($data['Status'] ?? '');
        if (!empty($status)) {
            $validStatuses = ['Deployed', 'Deployable', 'Ready', 'Pending', 'Archived', 'Repair', 'Diagnostics'];
            if (!in_array($status, $validStatuses)) {
                $this->addWarning("Row {$rowNumber}: Status '{$status}' is non-standard (common values: Deployed, Deployable, Ready, Pending)");
            }
        }

        // Warn about missing email for username
        if (!empty($username) && strpos($username, '@') === false) {
            // This is OK - email will be auto-generated
        }

        // Check for unusually long notes
        $notes = trim($data['Notes'] ?? '');
        if (strlen($notes) > 1000) {
            $this->addWarning("Row {$rowNumber}: Notes field is very long (" . strlen($notes) . " chars), may be truncated");
        }
    }

    /**
     * Get validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get validation warnings
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get validation statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Get human-readable validation report
     */
    public function getReport(): string
    {
        $report = "=== CSV VALIDATION REPORT ===\n\n";

        // Statistics
        $report .= "STATISTICS:\n";
        $report .= "  Total Data Rows: " . ($this->stats['total_data_rows'] ?? 0) . "\n";
        $report .= "  Valid Rows: " . ($this->stats['valid_rows'] ?? 0) . "\n";
        $report .= "  Empty Rows: " . ($this->stats['empty_rows'] ?? 0) . "\n";
        $report .= "  Unique Asset Tags: " . ($this->stats['asset_tags'] ?? 0) . "\n";
        $report .= "  Unique Serials: " . ($this->stats['serials'] ?? 0) . "\n\n";

        // Errors
        if (!empty($this->errors)) {
            $report .= "ERRORS (" . count($this->errors) . "):\n";
            foreach (array_slice($this->errors, 0, 50) as $error) {
                $report .= "  ✗ {$error}\n";
            }
            if (count($this->errors) > 50) {
                $report .= "  ... and " . (count($this->errors) - 50) . " more errors\n";
            }
            $report .= "\n";
        }

        // Warnings
        if (!empty($this->warnings)) {
            $report .= "WARNINGS (" . count($this->warnings) . "):\n";
            foreach (array_slice($this->warnings, 0, 50) as $warning) {
                $report .= "  ⚠ {$warning}\n";
            }
            if (count($this->warnings) > 50) {
                $report .= "  ... and " . (count($this->warnings) - 50) . " more warnings\n";
            }
            $report .= "\n";
        }

        // Validation result
        if (empty($this->errors)) {
            $report .= "✓ VALIDATION PASSED - File is ready for import\n";
        } else {
            $report .= "✗ VALIDATION FAILED - Fix errors before importing\n";
        }

        return $report;
    }

    /**
     * Add error message
     */
    private function addError(string $message): void
    {
        if (!in_array($message, $this->errors)) {
            $this->errors[] = $message;
        }
    }

    /**
     * Add warning message
     */
    private function addWarning(string $message): void
    {
        if (!in_array($message, $this->warnings)) {
            $this->warnings[] = $message;
        }
    }

    /**
     * Initialize statistics
     */
    private function initializeStats(): void
    {
        $this->stats = [
            'total_data_rows' => 0,
            'empty_rows' => 0,
            'valid_rows' => 0,
            'asset_tags' => 0,
            'serials' => 0,
        ];
    }

    /**
     * Check if validation passed
     */
    public function isPassed(): bool
    {
        return empty($this->errors);
    }

    /**
     * Check if there are warnings (but no errors)
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get first N errors
     */
    public function getErrorsLimit(int $limit = 10): array
    {
        return array_slice($this->errors, 0, $limit);
    }

    /**
     * Get first N warnings
     */
    public function getWarningsLimit(int $limit = 10): array
    {
        return array_slice($this->warnings, 0, $limit);
    }
}
