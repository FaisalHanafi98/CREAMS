---
name: password-reset
description: Resets a CREAMS user password via standalone PHP script. Use when login credentials are unknown or an account is locked out. Avoids the shell escaping bug in php artisan tinker.
disable-model-invocation: true
argument-hint: [email] [new-password]
---

# CREAMS Password Reset

Resets a staff member's password using a standalone PHP bootstrap script.

## Why Not `php artisan tinker --execute`?

`tinker --execute` breaks when the command string contains `$` variables inside a bash heredoc. Bash interpolates `$variable` before PHP ever sees it, causing parse errors or empty values. This is a persistent shell escaping issue with no clean workaround in heredoc syntax.

**Workaround**: Write a self-contained PHP file, execute it, then delete it.

## Steps

### 1. Write the reset script to scratchpad

Write this file (replace `$0` and `$1` with the actual email and password values before writing):

```php
<?php
// Reset CREAMS staff password
// Usage: php reset_password.php
// Delete this file after use.

chdir(__DIR__);
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$email = 'TARGET_EMAIL_HERE';
$newPassword = 'NEW_PASSWORD_HERE';

$user = \App\Models\Staff::where('email', $email)->first();

if (!$user) {
    echo "ERROR: No staff found with email: {$email}\n";
    exit(1);
}

$user->password = \Illuminate\Support\Facades\Hash::make($newPassword);
$user->save();

echo "SUCCESS: Password updated for {$email}\n";
echo "User: {$user->name} (ID: {$user->id})\n";
```

### 2. Place it in the CREAMS project root
The script uses relative paths (`__DIR__ . '/../vendor/autoload.php'`), so it must sit one level inside the CREAMS directory, or adjust paths accordingly. Simplest: write it directly to the CREAMS root.

### 3. Execute
```bash
cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS
php reset_password.php
```

### 4. Delete the script
Remove immediately after use. It contains a plaintext password.

### 5. Verify
Navigate to `http://localhost:8000/login` and confirm login with the new credentials.

## Known Credentials (current session)
- **Admin account**: `admin@creams.system` / `admin123`
- **Model**: `App\Models\Staff` (not `User` — CREAMS uses a custom Staff model)

## Common Pitfalls
- The model is `Staff`, not `User`. Laravel's default `User` model is not used for authentication.
- `vendor/autoload.php` path must be correct relative to where the script is placed.
- Always delete the script after use — it's a security risk if left in the repo.
