# Asset View Files - Controller References to Update

## New Asset View File Structure
All asset views have been consolidated and moved to: `resources/views/centres/assets/`

### New File Locations:
1. `centres/assets/index.blade.php` - Main asset listing (replaces multiple old files)
2. `centres/assets/create.blade.php` - Asset creation form
3. `centres/assets/edit.blade.php` - Asset editing form  
4. `centres/assets/show.blade.php` - Asset details view
5. `centres/assets/centre-assets.blade.php` - Centre-specific assets view

## Controller References That Need Updating:

### Asset Controller References:
**File: `app/Http/Controllers/Centre/AssetController.php`**
- Change `return view('assetmanagement', ...)` to `return view('centres.assets.index', ...)`
- Change `return view('assetmanagementregister', ...)` to `return view('centres.assets.create', ...)`
- Change `return view('assetmanagementupdate', ...)` to `return view('centres.assets.edit', ...)`
- Change `return view('assets.index', ...)` to `return view('centres.assets.index', ...)`
- Change `return view('assets.create', ...)` to `return view('centres.assets.create', ...)`
- Change `return view('assets.show', ...)` to `return view('centres.assets.show', ...)`
- Change `return view('assets.edit', ...)` to `return view('centres.assets.edit', ...)`

### Centre Controller References:
**File: `app/Http/Controllers/CentreController.php`**
- Change `return view('centres.assets', ...)` to `return view('centres.assets.centre-assets', ...)`

### Route References:
**File: `routes/web.php`**
- Update asset-related route names to use `centres.assets.*` prefix
- Example: `Route::get('assets', ...)` should point to centres/assets views

### Possible Additional Controllers:
**Check these controllers for asset view references:**
- `AdminController.php`
- `SupervisorController.php` 
- `DashboardController.php`
- Any controller that returns asset-related views

## Route Names to Update:
1. `route('assets.index')` → `route('centres.assets.index')`
2. `route('assets.create')` → `route('centres.assets.create')`
3. `route('assets.show', $id)` → `route('centres.assets.show', $id)`
4. `route('assets.edit', $id)` → `route('centres.assets.edit', $id)`
5. `route('centres.assets')` → `route('centres.assets.centre-assets')`

## CSS/JS References:
The new views use modern styling with pink/blue gradient theme. Old CSS files related to assets can be deprecated:
- `public/css/assetmanagementregisterstyle.css`
- `public/css/assetmanagementupdatestyle.css`
- Any other asset-specific CSS files

## Search for These Patterns in Controllers:
```bash
# Search for old view references
grep -r "assetmanagement" app/Http/Controllers/
grep -r "assets\." app/Http/Controllers/ 
grep -r "centres\.assets" app/Http/Controllers/
```

## Old Files Moved to Cleanup:
- `assetmanagement.blade.php` → `DELETE THESE FILES/OLD_ASSET_FILES/assetmanagement_OLD.blade.php`
- `assetmanagementregister.blade.php` → `DELETE THESE FILES/OLD_ASSET_FILES/assetmanagementregister_OLD.blade.php`
- `assetmanagementupdate.blade.php` → `DELETE THESE FILES/OLD_ASSET_FILES/assetmanagementupdate_OLD.blade.php`
- `assets/` folder → `DELETE THESE FILES/OLD_ASSET_FILES/assets_folder_OLD/`