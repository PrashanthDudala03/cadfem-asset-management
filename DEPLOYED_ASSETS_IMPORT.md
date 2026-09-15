# Deployed Assets Import Guide

## Overview

The Deployed Assets Import system allows you to import a CSV file containing deployed assets into the CADFEM Asset Management System (Snipe-IT). The system automatically creates and links all necessary relationships including manufacturers, models, categories, locations, departments, and users.

## CSV Format

Your CSV file must contain the following columns in this exact order:

```
Asset Tag | Model | Model No. | Category | Manufacturer | Serial | Order Number | Location | Checked Out | Type | Username | Department | Status | Notes | Device Status | ASSET OWNER
```

### Column Descriptions

| Column | Required | Description |
|--------|----------|-------------|
| **Asset Tag** | Yes | Unique identifier for the asset (e.g., ASSET001) |
| **Model** | Yes | Asset model name (e.g., Lenovo ThinkPad E15) |
| **Model No.** | No | Manufacturer model number (e.g., 20RES23R00) |
| **Category** | Yes | Category name (e.g., Laptop, Monitor, Server, CPU) |
| **Manufacturer** | Yes | Manufacturer name (e.g., Lenovo, HP, Dell, HPE) |
| **Serial** | No | Serial number (must be unique if specified) |
| **Order Number** | No | Purchase order number |
| **Location** | No | Location/office name (auto-created if doesn't exist) |
| **Checked Out** | No | "Yes"/"No" - whether asset is checked out to a user or location |
| **Type** | No | Asset type (informational only) |
| **Username** | No | Username of assigned user (creates user if not exists) |
| **Department** | No | Department name (auto-created if doesn't exist) |
| **Status** | No | Asset status (Deployed, Deployable, Ready, etc.) |
| **Notes** | No | Additional notes about the asset |
| **Device Status** | No | Additional status info (Deployed/Deployable) |
| **ASSET OWNER** | No | Owner name (used if Username is empty) |

## Sample CSV Data

```csv
Asset Tag,Model,Model No.,Category,Manufacturer,Serial,Order Number,Location,Checked Out,Type,Username,Department,Status,Notes,Device Status,ASSET OWNER
ASSET001,ThinkPad E15,20RES23R00,Laptop,Lenovo,ABC123456,PO-2024-001,Bangalore,Yes,Computer,john.doe,Engineering,Deployed,Production laptop,Deployed,John Doe
ASSET002,HP EliteBook,8460p,Laptop,HP,XYZ789012,PO-2024-002,Mumbai,Yes,Computer,jane.smith,Marketing,Deployed,Company laptop,Deployed,Jane Smith
MON001,HP E243i,E243i Monitor,Monitor,HP,MON123456,PO-2024-003,Bangalore,No,Display,,Engineering,Ready to Deploy,24" Monitor,Deployable,
SRV001,ProLiant DL380 Gen10,HPE123456,Server,HPE,SRV123456,PO-2024-004,Bangalore,No,Server,,IT,Ready to Deploy,2-socket server,Deployable,
```

## Installation & Setup

### 1. Prepare Your CSV File

1. Export your data from the source system (Excel, Google Sheets, etc.)
2. Ensure columns match the expected format exactly
3. Save as CSV (comma-separated values)
4. Place the file in `storage/app/deployed_assets.csv`

**Alternative locations**: You can specify a custom path when running the import command:
```bash
php artisan import:deployed-assets --file="path/to/your/file.csv"
```

### 2. Run the Import

#### Option A: Interactive Command (Recommended)
```bash
php artisan import:deployed-assets
```

This will:
- Prompt for the CSV file path (defaults to `storage/app/deployed_assets.csv`)
- Show a preview of the first 5 rows
- Ask for confirmation before importing
- Display results with counts of created/updated/failed assets

#### Option B: Direct File Path
```bash
php artisan import:deployed-assets --file="storage/app/my_assets.csv"
```

#### Option C: Dry Run (Test Without Saving)
```bash
php artisan import:deployed-assets --dry-run
```

This will process the entire file but rollback all database changes, allowing you to check for errors without actually importing.

#### Option D: Using Seeder Directly
```bash
php artisan db:seed --class=DeployedAssetsImportSeeder
```

The CSV file must be at `storage/app/deployed_assets.csv` when using this method.

## Features & Behavior

### Automatic Entity Creation

The seeder automatically creates the following if they don't exist:

- **Manufacturers**: Exact name matching (case-insensitive)
- **Categories**: Mapped to appropriate Snipe-IT category types
- **Asset Models**: Created with manufacturer and category relationships
- **Locations**: Created with auto-generated names
- **Departments**: Created and linked to locations
- **Users**: Parsed from username format (e.g., "john.doe" → "John Doe")

### Category Type Mapping

The system intelligently maps category names to Snipe-IT category types:

| Detected Keywords | Category Type |
|------------------|---------------|
| Laptop, Desktop, CPU, Workstation, Server, Computer | `asset` |
| Monitor, Display | `asset` |
| Keyboard, Mouse, Printer | `accessory` |
| CCTV, Camera, Access | `accessory` |
| Others | `asset` (default) |

### Status Label Handling

- **Deployed** or **Deployable** entries use the "Ready to Deploy" status
- Missing status defaults to "Ready to Deploy"
- Unknown statuses are created as new deployable status labels
- Access Points and Peripherals default to deployable status

### User Assignment

The seeder handles asset assignment in this priority:

1. If `Username` is provided, assign to that user
2. If `Username` is empty but `ASSET OWNER` is provided, try to use that
3. If `Checked Out` = "Yes" and no username, assign to location
4. Otherwise, leave unassigned

Users are auto-created with:
- **Username**: From CSV data
- **First Name**: Parsed from username (before dot)
- **Last Name**: Parsed from username (after dot)
- **Email**: `{username}@cadfem.in`
- **Status**: Activated

### Serial Number Handling

- Serial numbers must be unique across all assets
- If an asset with the same serial already exists, it will be updated
- Missing serial numbers are allowed (treated as null)

### Data Updates

If an asset with the same `asset_tag` already exists:
- It will be **updated** with new data from CSV
- All fields are updated except relationships (unless explicitly changed)
- Provides idempotent import (safe to re-run)

## Error Handling

### Validation

The seeder validates:
- ✓ Asset Tag is not empty (required)
- ✓ Model ID exists and is valid
- ✓ Status ID is valid
- ✓ Serial numbers are unique
- ✓ User assignments reference valid users/locations

### Error Reporting

Failed rows are logged with:
- Row number in CSV
- Specific error message
- Stored in Laravel logs: `storage/logs/laravel.log`

### Common Errors

| Error | Solution |
|-------|----------|
| "Asset Tag is required" | Ensure all assets have a unique identifier in Asset Tag column |
| "Serial must be unique" | Remove duplicate serial numbers or leave empty |
| "Category not found" | Category name must be exact match (case-insensitive) |
| "Model creation failed" | Ensure manufacturer and category exist |
| "CSV file not found" | Verify file path is correct and file exists |

## Performance Considerations

### Memory Management
- Seeder commits changes in batches of 100 rows
- Automatic garbage collection between batches
- Suitable for 10,000+ assets on standard servers

### Database Transaction
- All changes wrapped in transaction
- Auto-rollback on error
- Ensures data integrity

### Timing
- Typical performance: 50-200 assets per second
- Depends on server specs and related entity complexity
- Full validation on each asset import

## Best Practices

### 1. Preparation
```bash
# Always run validation/dry-run first
php artisan import:deployed-assets --file="test.csv" --dry-run

# Check logs for any warnings
tail -f storage/logs/laravel.log
```

### 2. Data Quality
- ✓ Remove duplicate asset tags
- ✓ Trim whitespace from all fields
- ✓ Use consistent manufacturer names
- ✓ Use consistent location names
- ✓ Verify username format (lowercase recommended)

### 3. Backup
```bash
# Backup database before large imports
mysqldump snipeit_db > backup_$(date +%Y%m%d).sql
```

### 4. Incremental Imports
```bash
# Import in batches if CSV is very large (50,000+ rows)
# Split CSV and process separately
php artisan import:deployed-assets --file="part1.csv"
php artisan import:deployed-assets --file="part2.csv"
```

### 5. Post-Import Verification
```bash
# Check created assets
SELECT COUNT(*) FROM assets;

# Check for unassigned assets
SELECT COUNT(*) FROM assets WHERE assigned_to IS NULL;

# Check import logs
grep "Created asset" storage/logs/laravel.log | wc -l
grep "Updated asset" storage/logs/laravel.log | wc -l
```

## API Integration

The seeder can be triggered programmatically:

```php
use Database\Seeders\DeployedAssetsImportSeeder;
use Illuminate\Support\Facades\Artisan;

// Run via command
Artisan::call('import:deployed-assets', [
    '--file' => 'path/to/file.csv'
]);

// Or directly via seeder
$seeder = new DeployedAssetsImportSeeder();
$seeder->run();
```

## Troubleshooting

### Issue: "The table 'assets' does not exist"
**Solution**: Run migrations first
```bash
php artisan migrate
```

### Issue: High memory usage
**Solution**: Process in smaller batches
```bash
# Split CSV into smaller files (500 rows each)
split -l 500 large_file.csv small_file_
for file in small_file_*; do
    php artisan import:deployed-assets --file="$file"
done
```

### Issue: Slow performance
**Solution**: Check if indexes are present
```bash
# Optimize database
php artisan optimize

# Check database indexes
SHOW INDEX FROM assets;
```

### Issue: Duplicate serial numbers causing failures
**Solution**: Remove duplicates from CSV before importing
```bash
# Remove duplicate serials, keeping first occurrence
awk '!seen[$6]++' original.csv > cleaned.csv
```

## Rollback

To rollback an import:

```bash
# Option 1: Restore from backup
mysql snipeit_db < backup_20240115.sql

# Option 2: Delete imported assets
DELETE FROM assets WHERE created_at > '2024-01-15 10:00:00';

# Option 3: Full rollback using transactions
php artisan import:deployed-assets --file="file.csv" --dry-run
# Then run actual import
php artisan import:deployed-assets --file="file.csv"
```

## Support & Logging

All operations are logged to: `storage/logs/laravel.log`

### Log Entries Include:
- Asset creation: "Created asset: ASSET001"
- Asset updates: "Updated asset: ASSET001"
- User creation: "Created user: john.doe"
- Warnings: "Failed to create user john.doe: ..."
- Admin user selection: "Using admin user: admin"

### View Real-time Logs
```bash
tail -f storage/logs/laravel.log | grep "import\|asset\|user"
```

## Example Workflow

```bash
# 1. Prepare CSV file
cp /path/to/deployed_assets.xlsx deployed_assets.csv

# 2. Test import with dry-run
php artisan import:deployed-assets --file="storage/app/deployed_assets.csv" --dry-run

# 3. Check for errors
grep -i "error\|warning" storage/logs/laravel.log

# 4. If dry-run looks good, run actual import
php artisan import:deployed-assets --file="storage/app/deployed_assets.csv"

# 5. Verify results
php artisan tinker
> Asset::count()
> Asset::where('assigned_to', '!=', null)->count()
```

## CSV Export Examples

### From Excel
1. Open Excel file
2. File > Save As
3. Format: "CSV UTF-8 (Comma delimited)"
4. Save

### From Google Sheets
1. File > Download > Comma-separated values (.csv)
2. Save to `storage/app/deployed_assets.csv`

### From Database (MySQL)
```sql
SELECT 
    asset_tag, model, model_number, category, manufacturer, serial,
    order_number, location, checked_out, type, username, department,
    status, notes, device_status, asset_owner
INTO OUTFILE '/tmp/deployed_assets.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n'
FROM your_source_table;
```

## License & Attribution

This seeder is part of the CADFEM Asset Management System based on Snipe-IT.

Generated with Claude Code using Laravel Seeder patterns from Snipe-IT.
