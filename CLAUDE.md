# CLAUDE.md - CREAMS Development Guide & Rules

This document serves as the authoritative guide for Claude Code when working with the CREAMS (Community-based REhAbilitation Management System) codebase. Consider this your law book - follow these rules and patterns for ALL development work.

## 🚨 SUPREME LAW: VERIFICATION BEFORE DECLARATION

### The Golden Rule
**NEVER claim something is fixed without proving it works.**

### Mandatory Verification Steps
Before saying "I've fixed the issue" or "This should work now", you MUST:

1. **Run the actual code** and show the output
2. **Test in multiple ways** (tinker, browser, curl)
3. **Check the logs** for any errors
4. **Verify the database** state if applicable
5. **Show your proof** with actual command outputs

### Verification Output Format
```bash
# When claiming a fix, always show:
CLAIM: Fixed [specific issue]

VERIFICATION:
1. Database check:
   $ php artisan tinker
   >>> [relevant query]
   => [actual output]

2. Route test:
   $ curl -X GET/POST [url]
   Response: [status and key data]

3. Log check:
   $ tail -20 storage/logs/laravel.log
   [No errors related to this issue]

4. Browser test:
   - Visited [URL]
   - Performed [action]
   - Result: [what happened]
```

## 📖 FUNDAMENTAL LAWS OF CREAMS DEVELOPMENT

### Law 1: Database Truth
**The database migrations are the source of truth, not your assumptions.**

```bash
# ALWAYS check before using any column:
cat database/migrations/*[table_name]*.php
php artisan tinker
>>> Schema::getColumnListing('[table_name]')
```

### Law 2: Authentication Heresy
**NEVER use Laravel's default Auth system.**

```php
// ❌ FORBIDDEN - These will break everything:
auth()->user()
Auth::user()
$request->user()
Auth::check()

// ✅ THE ONLY WAY:
session('id')      // Current user ID
session('role')    // Current user role
session('name')    // Current user name
session('centre_id') // Current user's centre
```

### Law 3: Centre Isolation Doctrine
**Every query must respect centre boundaries.**

```php
// For non-admin users, ALWAYS filter by centre:
public function getData()
{
    if (session('role') === 'admin') {
        return Model::all(); // Admin sees all
    }
    
    return Model::where('centre_id', session('centre_id'))->get();
}
```

### Law 4: Relationship Verification
**Never trust a relationship exists - verify it.**

```php
// Before using any relationship:
$model = Model::find($id);
if (!$model->relationshipName()->exists()) {
    // Handle missing relationship
}
```

### Law 5: Migration Sanctity
**NEVER modify existing migrations. Create new ones.**

```bash
# To change a table:
php artisan make:migration add_column_to_table --table=table_name
php artisan make:migration modify_column_in_table --table=table_name
# NEVER edit the original create_table migration
```

## 🛠️ DEVELOPMENT METHODOLOGY

### Step 1: Reconnaissance
Before touching ANY code:

```bash
# 1. Understand the current structure
ls -la app/Http/Controllers/ | grep -i [feature]
ls -la app/Models/ | grep -i [feature]
find resources/views -name "*[feature]*"

# 2. Check existing routes
php artisan route:list | grep -i [feature]

# 3. Examine the database
php artisan tinker
>>> Schema::getColumnListing('[table_name]')
>>> Model::first() // See actual data structure

# 4. Review relationships
>>> Model::first()->relatedModel
>>> Model::with('relations')->first()
```

### Step 2: Planning
Create a plan BEFORE coding:

```markdown
## Implementation Plan for [Feature]

### Current State:
- Database tables involved: [list them]
- Models involved: [list them]
- Controllers involved: [list them]
- Key relationships: [list them]

### Changes Needed:
1. [Specific change with reason]
2. [Specific change with reason]

### Verification Strategy:
- [ ] Test existing functionality first
- [ ] Make changes incrementally
- [ ] Test after each change
- [ ] Verify no side effects
```

### Step 3: Implementation
Follow the incremental approach:

1. **Make ONE small change**
2. **Test that change**
3. **Commit if working**
4. **Repeat**

### Step 4: Verification
Use the Verification Protocol from the Supreme Law.

## 🔍 INVESTIGATION PROTOCOLS

### When Encountering "Column not found" Errors

```bash
# Step 1: Check the migration
find database/migrations -name "*[table_name]*" -exec cat {} \;

# Step 2: Check actual database
php artisan tinker
>>> DB::select('DESCRIBE table_name');

# Step 3: Check model attributes
>>> Model::first()->getAttributes();

# Step 4: Fix the query to use correct column name
```

### When Relationships Return Null

```bash
# Step 1: Verify foreign key has value
>>> $model = Model::find($id);
>>> $model->foreign_key_id; // Should not be null

# Step 2: Check related record exists
>>> RelatedModel::find($model->foreign_key_id);

# Step 3: Verify relationship definition
# Check the model file for correct relationship syntax

# Step 4: Test with eager loading
>>> Model::with('relationship')->find($id);
```

### When Routes Don't Work

```bash
# Step 1: Verify route exists
php artisan route:list | grep [route_name]

# Step 2: Check middleware
# Look for role restrictions in route definition

# Step 3: Test with curl
curl -X GET/POST http://localhost:8000/[route] -H "Cookie: [session_cookie]"

# Step 4: Check controller method exists
# Verify the controller@method referenced in route exists
```

## 🏗️ CODE PATTERNS & CONVENTIONS

### Controller Pattern
```php
public function methodName(Request $request)
{
    try {
        // 1. Authentication check
        if (!session()->has('id')) {
            return redirect()->route('login');
        }
        
        // 2. Authorization check
        if (!in_array(session('role'), ['allowed', 'roles'])) {
            abort(403);
        }
        
        // 3. Input validation
        $validated = $request->validate([
            'field' => 'required|string',
        ]);
        
        // 4. Business logic with centre scoping
        $data = Model::where('centre_id', session('centre_id'))
                    ->where('other_conditions', $value)
                    ->get();
        
        // 5. Return response
        return view('module.view', compact('data'));
        
    } catch (\Exception $e) {
        Log::error('Error in ControllerName@methodName', [
            'user_id' => session('id'),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return back()->with('error', 'An error occurred');
    }
}
```

### Model Pattern
```php
class ModelName extends Model
{
    // 1. Table name if not following convention
    protected $table = 'table_name';
    
    // 2. Mass assignable fields
    protected $fillable = ['field1', 'field2'];
    
    // 3. Hidden fields
    protected $hidden = ['password', 'remember_token'];
    
    // 4. Casts
    protected $casts = [
        'date_field' => 'date',
        'boolean_field' => 'boolean',
    ];
    
    // 5. Relationships (ALWAYS verify foreign keys exist)
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class, 'foreign_key_id');
    }
    
    // 6. Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // 7. Accessors/Mutators
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

### Query Pattern
```php
// ALWAYS use this pattern for queries:

// 1. Start with centre scoping (unless admin viewing all)
$query = Model::query();

if (session('role') !== 'admin') {
    $query->where('centre_id', session('centre_id'));
}

// 2. Add other conditions
$query->where('status', 'active')
      ->where('date', '>=', now());

// 3. Include relationships to avoid N+1
$query->with(['relationship1', 'relationship2']);

// 4. Order and paginate
$results = $query->orderBy('created_at', 'desc')
                ->paginate(20);
```

## 🧪 TESTING PROTOCOLS

### Before Any Feature is "Complete"

```bash
# 1. Unit Test in Tinker
php artisan tinker
>>> # Test your model methods
>>> # Test your relationships
>>> # Test your scopes

# 2. Feature Test via Browser
- Login as each role type
- Try the feature
- Try to break it (invalid input, wrong permissions)

# 3. API Test via Curl
curl -X POST http://localhost:8000/api/endpoint \
  -H "Content-Type: application/json" \
  -d '{"key": "value"}'

# 4. Check Logs
tail -100 storage/logs/laravel.log | grep -i error

# 5. Performance Check
php artisan tinker
>>> DB::enableQueryLog();
>>> # Run your code
>>> DB::getQueryLog(); // Check for N+1 queries
```

### Error Handling Verification

```php
// Test these scenarios:
1. Unauthenticated access
2. Unauthorized access (wrong role)
3. Invalid input data
4. Missing related records
5. Database constraints (duplicate entries, foreign key violations)
6. Centre isolation (trying to access other centre's data)
```

## 🚑 EMERGENCY PROCEDURES

### When Everything is Broken

```bash
# 1. Clear all caches
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 2. Regenerate autoload
composer dump-autoload

# 3. Check file permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# 4. Check the logs
tail -100 storage/logs/laravel.log

# 5. Verify database connection
php artisan tinker
>>> DB::connection()->getPdo();

# 6. Last resort - fresh install (DEVELOPMENT ONLY)
php artisan migrate:fresh --seed
```

### When Relationships are Corrupted

```bash
# Audit foreign keys
php artisan tinker
>>> DB::select("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME, 
        CONSTRAINT_NAME, 
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_SCHEMA = 'creams'
");
```

## 📏 CODING STANDARDS

### Naming Conventions
- **Controllers**: PascalCase with "Controller" suffix
- **Models**: PascalCase singular (User, not Users)
- **Tables**: snake_case plural (users, activity_sessions)
- **Columns**: snake_case (first_name, is_active)
- **Routes**: kebab-case (user-profile, activity-sessions)
- **Views**: snake_case in folders (activities/index.blade.php)

### Documentation Requirements
```php
/**
 * Every method should have a docblock explaining:
 * - What it does
 * - What parameters it expects
 * - What it returns
 * - Any side effects
 * 
 * @param Request $request
 * @return \Illuminate\Http\Response
 * @throws \Exception
 */
```

## 🎯 SYSTEM CONSTANTS

### User Roles
```php
const ROLES = ['admin', 'supervisor', 'teacher', 'ajk'];
const ADMIN_ROLES = ['admin'];
const MANAGEMENT_ROLES = ['admin', 'supervisor'];
const TEACHING_ROLES = ['teacher'];
const SUPPORT_ROLES = ['ajk'];
```

### Status Values
```php
// Common status patterns across the system
const ACTIVE_STATUSES = ['active', 'ongoing', 'scheduled'];
const INACTIVE_STATUSES = ['inactive', 'completed', 'cancelled'];
const PENDING_STATUSES = ['pending', 'draft', 'scheduled'];
```

## 🔐 SECURITY CHECKLIST

For EVERY feature:
- [ ] Authentication required?
- [ ] Authorization checked?
- [ ] Input validated?
- [ ] SQL injection prevented (use Eloquent/Query Builder)?
- [ ] XSS prevented (use `{{ }}` not `{!! !!}`)?
- [ ] CSRF token included in forms?
- [ ] Centre isolation enforced?
- [ ] Sensitive data not exposed in logs?
- [ ] File uploads validated and sanitized?
- [ ] Rate limiting needed?

## 📝 COMMIT MESSAGE FORMAT

```
[Type] Brief description

- Detailed point 1
- Detailed point 2

Affects: [Modules affected]
Testing: [How to test]
```

Types: `[Feature]`, `[Fix]`, `[Refactor]`, `[Security]`, `[Performance]`, `[Docs]`

## 🎪 FINAL WISDOM

1. **Read existing code first** - Someone probably solved this already
2. **Test incrementally** - Big bang changes always break
3. **Document weird stuff** - Future you will thank present you
4. **Ask for clarification** - Better to ask than assume
5. **Centre isolation is sacred** - Never compromise on this
6. **The database never lies** - When in doubt, check the data
7. **Logs are your friend** - They tell the real story

---

**Remember**: This document is your law book. When in doubt, refer back to these principles. When making decisions, follow these patterns. When claiming success, prove it with these methods.