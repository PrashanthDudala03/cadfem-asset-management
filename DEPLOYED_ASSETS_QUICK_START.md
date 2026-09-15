# Deployed Assets Import - Quick Start Guide

## 30-Second Setup

```bash
# 1. Place your CSV file in the right location
cp /path/to/your/file.csv storage/app/deployed_assets.csv

# 2. Validate the CSV (recommended)
php artisan validate:deployed-assets

# 3. Import the assets
php artisan import:deployed-assets

# Done! Check results:
php artisan tinker
> Asset::count()
```

## CSV Format (Required Columns)

Your CSV **must** have these columns (in any order):

```
Asset Tag | Model | Model No. | Category | Manufacturer | Serial | Order Number | 
Location | Checked Out | Type | Username | Department | Status | Notes | 
Device Status | ASSET OWNER
```

### Minimum Required Fields Per Row
- **Asset Tag** - Unique identifier (REQUIRED)
- **Model** - Model name (REQUIRED)
- **Category** - Category name (REQUIRED)
- **Manufacturer** - Manufacturer name (REQUIRED)

## Usage Examples

### Example 1: Simple Import
```bash
# Copy sample CSV
cp storage/app/deployed_assets_sample.csv storage/app/deployed_assets.csv

# Import
php artisan import:deployed-assets
```

### Example 2: Import from Custom Path
```bash
php artisan import:deployed-assets --file="/home/user/assets.csv"
```

### Example 3: Validate Before Importing
```bash
# Validation with full report
php artisan validate:deployed-assets --file="storage/app/assets.csv"

# Quick summary
php artisan validate:deployed-assets --file="storage/app/assets.csv" --summary

# Show only errors
php artisan validate:deployed-assets --file="storage/app/assets.csv" --errors-only
```

### Example 4: Dry Run (Test Without Saving)
```bash
php artisan import:deployed-assets --file="storage/app/assets.csv" --dry-run
```

## Sample CSV Data

### Simple Example (Minimal Fields)
```csv
Asset Tag,Model,Category,Manufacturer
ASSET001,ThinkPad E15,Laptop,Lenovo
ASSET002,HP EliteBook,Laptop,HP
MON001,HP E243i,Monitor,HP
```

### Complete Example (All Fields)
```csv
Asset Tag,Model,Model No.,Category,Manufacturer,Serial,Order Number,Location,Checked Out,Type,Username,Department,Status,Notes,Device Status,ASSET OWNER
ASSET001,ThinkPad E15,20RES23R00,Laptop,Lenovo,ABC123456789,PO-2024-001,Bangalore,Yes,Computer,john.doe,Engineering,Deployed,Primary work laptop,Deployed,John Doe
ASSET002,HP EliteBook,8460p,Laptop,HP,XYZ789012,PO-2024-002,Mumbai,Yes,Computer,jane.smith,Marketing,Deployed,Backup laptop,Deployed,Jane Smith
MON001,HP E243i,E243i,Monitor,HP,MON123456,PO-2024-003,Bangalore,No,Display,,Engineering,Ready to Deploy,24" Monitor,Deployable,
```

## Supported Asset Types

The seeder recognizes these asset types:

### Computing Devices (Assets)
- Laptop, Desktop, CPU, Workstation, Server, Computer
- Automatically categorized as `asset`

### Peripherals & Accessories
- Monitor, Display, Keyboard, Mouse, Printer
- CCTV, Camera, Access Point, Access Control
- Automatically categorized as `asset` or `accessory`

### Servers
- ProLiant (HPE), PowerEdge (Dell), etc.
- Automatically categorized as `asset`

## Manufacturers Recognized

Common manufacturers are auto-created:

**Laptops**: Lenovo, HP, Dell, ASUS, Acer, Apple, Microsoft, Razer

**Desktops/Servers**: Dell, HP, Lenovo, HPE, Super Micro, Cisco

**Monitors**: HP, Dell, BenQ, ASUS, LG, Acer

**Networking**: Cisco, Ubiquiti, Netgear, D-Link

**Security**: Hikvision, Dahua, Axis, Canon

## What Gets Created Automatically

When you import, the seeder creates:

- ✓ Manufacturers (if don't exist)
- ✓ Categories (if don't exist)
- ✓ Asset Models (links to manufacturer & category)
- ✓ Locations (if don't exist)
- ✓ Departments (if don't exist)
- ✓ Users (if referenced but don't exist)
- ✓ Assets (with all relationships)

## Status Handling

CSV Status → Snipe-IT Status

| CSV Status | Result |
|-----------|--------|
| Deployed | "Ready to Deploy" |
| Deployable | "Ready to Deploy" |
| Empty/Missing | "Ready to Deploy" |
| Ready | "Ready to Deploy" |
| Pending | "Pending" (if exists) |
| Other | Creates new status label |

## User Assignment

The seeder assigns assets in this priority:

1. If `Username` is provided → assign to that user
2. Else if `ASSET OWNER` is provided → try to assign to that user
3. Else if `Checked Out = "Yes"` → assign to location
4. Else → leave unassigned

**Note**: Users are auto-created with:
- Email: `{username}@cadfem.in`
- Name parsed from username (e.g., "john.doe" → "John Doe")

## Common Workflows

### Import Laptops from Your Inventory

**Step 1: Prepare CSV**
```csv
Asset Tag,Model,Model No.,Category,Manufacturer,Serial,Location,Username,Department
LAP001,ThinkPad E15,20RES23R00,Laptop,Lenovo,ABC123456789,Bangalore,user1,IT
LAP002,HP EliteBook,8460p,Laptop,HP,DEF987654321,Mumbai,user2,Engineering
```

**Step 2: Validate**
```bash
php artisan validate:deployed-assets --file="laptops.csv"
```

**Step 3: Import**
```bash
php artisan import:deployed-assets --file="laptops.csv"
```

### Bulk Import Multiple Categories

```bash
# Split large file by category
grep "Laptop," all_assets.csv > laptops.csv
grep "Monitor," all_assets.csv > monitors.csv
grep "Server," all_assets.csv > servers.csv

# Import each
php artisan import:deployed-assets --file="laptops.csv"
php artisan import:deployed-assets --file="monitors.csv"
php artisan import:deployed-assets --file="servers.csv"

# Verify
php artisan tinker
> Asset::count()
> Asset::where('category_id', <laptop_id>)->count()
```

### Import with Department Assignments

```csv
Asset Tag,Model,Category,Manufacturer,Location,Department,Username
DESK001,OptiPlex 7090,Desktop,Dell,Bangalore,Engineering,dev_lead
DESK002,Z440 Workstation,Desktop,HP,Bangalore,Design,designer_01
```

## Troubleshooting

### Issue: "CSV file not found"
**Solution**: Copy your CSV to `storage/app/deployed_assets.csv`
```bash
cp your_file.csv storage/app/deployed_assets.csv
```

### Issue: "Asset Tag is required"
**Solution**: Ensure all rows have Asset Tag filled in first column

### Issue: "Duplicate Asset Tag"
**Solution**: Ensure each asset has unique Asset Tag (will update if same tag)

### Issue: "Duplicate Serial Number"
**Solution**: Remove duplicate serials or leave empty if not unique

### Issue: High Memory Usage
**Solution**: Split large CSV into smaller files
```bash
# Split into 1000-line chunks
split -l 1000 large.csv chunk_

for file in chunk_*; do
    php artisan import:deployed-assets --file="$file"
done
```

### Issue: Users Not Creating
**Solution**: Check username format - should be alphanumeric (e.g., john.doe)
```bash
# Good usernames
john.doe
jane_smith
user123

# Bad usernames (will warn)
john@example.com  # Use just the local part
```

## Validation Checklist

Before importing, ensure:

- ✓ CSV file exists and is readable
- ✓ First row contains all required headers
- ✓ Asset Tag column has unique values
- ✓ Serial numbers are unique (or empty)
- ✓ No special characters in asset tags
- ✓ Manufacturer names are consistent
- ✓ Category names are consistent
- ✓ Username format is correct (alphanumeric + dots/underscores)

## Performance Tips

### For Large Imports (1000+ assets)

```bash
# 1. Validate first
php artisan validate:deployed-assets --file="large_file.csv" --summary

# 2. Split into batches if very large
split -l 1000 large_file.csv batch_

# 3. Import each batch
for file in batch_*; do
    php artisan import:deployed-assets --file="$file"
    # Add small delay between batches
    sleep 5
done

# 4. Verify
php artisan tinker
> Asset::count()
```

### Database Optimization

```bash
# Before large import
php artisan optimize
php artisan config:cache

# After import
php artisan optimize:clear
```

## Rollback/Undo

If import goes wrong:

```bash
# Option 1: Dry run first
php artisan import:deployed-assets --file="file.csv" --dry-run

# Option 2: Delete imported assets
php artisan tinker
> Asset::where('created_at', '>', now()->subHours(1))->delete()

# Option 3: Restore from backup
mysql snipeit_db < backup.sql
```

## Verifying Import Success

```bash
# Check import logs
tail -f storage/logs/laravel.log | grep -i "asset\|import"

# Quick stats
php artisan tinker

# Count total assets
> Asset::count()

# Count assigned assets
> Asset::whereNotNull('assigned_to')->count()

# Count unassigned
> Asset::whereNull('assigned_to')->count()

# Count by category
> Asset::with('category')->groupBy('category.name')->selectRaw('category.name, count(*) as total')->get()

# Find import errors
> \Illuminate\Support\Facades\Log::read()->filter(fn($line) => str_contains($line, 'error'));
```

## API Usage (Programmatic)

```php
use App\Console\Commands\ImportDeployedAssets;
use App\Services\DeployedAssetsValidator;
use Illuminate\Support\Facades\Artisan;

// Via command
Artisan::call('import:deployed-assets', [
    '--file' => 'storage/app/file.csv'
]);

// Or validate first
$validator = new DeployedAssetsValidator('storage/app/file.csv');
if ($validator->validate()) {
    // Proceed with import
    Artisan::call('import:deployed-assets', ['--file' => 'storage/app/file.csv']);
} else {
    $errors = $validator->getErrors();
    // Handle errors
}
```

## Next Steps

After successful import:

1. **Verify in UI**: Navigate to Assets list in Snipe-IT
2. **Assign Remaining**: Some assets may need manual assignment
3. **Add Images**: Upload device images in Asset details
4. **Configure Checkout**: Set up checkout rules per category
5. **Backup**: Back up the database

## Support

For issues or questions:

1. Check logs: `storage/logs/laravel.log`
2. Run validation: `php artisan validate:deployed-assets --file="file.csv"`
3. Test with sample: `cp storage/app/deployed_assets_sample.csv storage/app/deployed_assets.csv`
4. Review documentation: `DEPLOYED_ASSETS_IMPORT.md`

## Files Included

- `database/seeders/DeployedAssetsImportSeeder.php` - Main seeder
- `app/Console/Commands/ImportDeployedAssets.php` - Import command
- `app/Console/Commands/ValidateDeployedAssets.php` - Validation command
- `app/Services/DeployedAssetsValidator.php` - Validation service
- `storage/app/deployed_assets_sample.csv` - Sample data
- `DEPLOYED_ASSETS_IMPORT.md` - Full documentation

## Version Info

- **Created**: September 2024
- **Tested**: Snipe-IT 6.x
- **Laravel**: 9.x+
- **PHP**: 8.0+

---

**Made with Claude Code** - Production-ready Laravel seeder for Snipe-IT asset imports
