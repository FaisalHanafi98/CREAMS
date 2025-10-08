# 🎉 Toast Notification System Documentation

**Last Updated:** January 2025
**Status:** ✅ Fully Implemented
**Location:** `resources/views/components/toast-notifications.blade.php`

---

## 📋 Overview

The CREAMS system now uses a **modern toast notification system** that displays messages in the **top-right corner** of the screen. These notifications:

- ✅ Auto-dismiss after 5 seconds
- ✅ Show beautiful animated progress bars
- ✅ Don't push page content down
- ✅ Stack nicely when multiple messages appear
- ✅ Work on mobile devices
- ✅ Support dark mode

**NO MORE** ugly green/red banners at the top of the page that duplicate!

---

## 🚀 Quick Start

### For Developers Using Controllers

**Just use the standard Laravel flash messages** - they automatically become toasts!

```php
// ✅ Success messages
return redirect()->route('activities.index')
    ->with('success', 'Activity created successfully!');

// ✅ Error messages
return redirect()->back()
    ->with('error', 'Failed to save activity. Please try again.');

// ✅ Warning messages
return redirect()->route('trainees.edit', $id)
    ->with('warning', 'This trainee has pending approvals.');

// ✅ Info messages
return redirect()->route('dashboard')
    ->with('info', 'System maintenance scheduled for tonight.');

// ✅ Fail messages (shown as error)
return redirect()->back()
    ->with('fail', 'Operation failed.');
```

**That's it!** The toast system handles everything automatically.

---

## 💻 Usage in Blade Templates

The toast notification system is **automatically included** in `layouts/app.blade.php`, so all views that extend this layout will have toast notifications.

```blade
@extends('layouts.app')

@section('content')
    {{-- Your content here --}}
    {{-- Toasts will automatically appear for any session flash messages --}}
@endsection
```

---

## 🎨 Manual JavaScript Usage

If you need to trigger toasts from JavaScript (e.g., AJAX operations):

```javascript
// Success notification
ToastNotification.success('Data saved successfully!');

// Error notification
ToastNotification.error('Unable to connect to server.');

// Warning notification
ToastNotification.warning('Your session will expire in 5 minutes.');

// Info notification
ToastNotification.info('New features are available!');

// Custom duration (in milliseconds)
ToastNotification.success('Quick message!', 3000); // 3 seconds
ToastNotification.error('Important error!', 10000); // 10 seconds
```

---

## 📱 Examples

### Example 1: Activity Controller Success

```php
public function store(Request $request)
{
    try {
        $activity = Activity::create($validated);

        return redirect()->route('activities.show', $activity->id)
            ->with('success', 'Activity created successfully!');
            // Shows green toast: "Success | Activity created successfully!"

    } catch (Exception $e) {
        return redirect()->back()
            ->withInput()
            ->with('error', 'Failed to create activity: ' . $e->getMessage());
            // Shows red toast: "Error | Failed to create activity..."
    }
}
```

### Example 2: Asset Update with Warning

```php
public function update(Request $request, Asset $asset)
{
    $asset->update($validated);

    if ($asset->warranty_expiry < now()->addDays(30)) {
        return redirect()->route('assets.show', $asset->id)
            ->with('warning', 'Asset warranty expires soon!');
            // Shows yellow toast with warning icon
    }

    return redirect()->route('assets.show', $asset->id)
        ->with('success', 'Asset updated successfully!');
}
```

### Example 3: AJAX Request with Toast

```javascript
// In your JavaScript file
fetch('/api/trainees/enroll', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ trainee_id: traineeId, activity_id: activityId })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        ToastNotification.success('Trainee enrolled successfully!');
        // Refresh table or update UI
    } else {
        ToastNotification.error(data.message || 'Enrollment failed');
    }
})
.catch(error => {
    ToastNotification.error('Network error. Please try again.');
});
```

---

## 🎯 Best Practices

### ✅ DO:

- **Use clear, concise messages** - Users will only see them for 5 seconds
- **Be specific** - "Activity created successfully" is better than "Success"
- **Use appropriate types** - Don't use warnings for errors
- **Test on mobile** - Toasts are responsive but check they work well

```php
// ✅ GOOD
->with('success', 'Trainee "Ahmad bin Ali" enrolled in activity "Art Therapy"')

// ❌ BAD
->with('success', 'Operation completed successfully')
```

### ❌ DON'T:

- **Don't write essays** - Keep messages under 100 characters
- **Don't use technical jargon** - Users don't care about "database constraints"
- **Don't forget to show messages** - Every user action should have feedback
- **Don't use the old alert-success/alert-danger divs** - They're deprecated

```php
// ❌ BAD - Too technical
->with('error', 'SQLSTATE[23000]: Integrity constraint violation')

// ✅ GOOD - User-friendly
->with('error', 'Cannot delete activity with enrolled trainees')
```

---

## 🛠️ Customization

### Changing Toast Duration

```javascript
// Default is 5000ms (5 seconds)
ToastNotification.success('Message', 3000);  // 3 seconds
ToastNotification.error('Important!', 10000); // 10 seconds
```

### Styling

Toast colors are defined in `/resources/views/components/toast-notifications.blade.php`:

```css
/* Success - Green */
.toast-success { /* #28a745 */ }

/* Error - Red */
.toast-error { /* #dc3545 */ }

/* Warning - Yellow */
.toast-warning { /* #ffc107 */ }

/* Info - Blue */
.toast-info { /* #17a2b8 */ }
```

---

## 🔄 Migration from Old System

### Old Flash Messages (DEPRECATED)

```blade
{{-- ❌ OLD - Don't use anymore --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
```

### New Toast System (CURRENT)

```blade
{{-- ✅ NEW - Automatic! --}}
@include('components.toast-notifications')

{{-- Or use the backward-compatible component --}}
@include('components.flash-messages')
{{-- This now shows toasts instead of banners --}}
```

**Note:** `flash-messages.blade.php` now redirects to the toast system for backward compatibility.

---

## 🐛 Troubleshooting

### Problem: Toasts Not Showing

**Check 1:** Is `toast-notifications` included in your layout?

```blade
@include('components.toast-notifications')
```

**Check 2:** Are you extending `layouts/app.blade.php`?

```blade
@extends('layouts.app') {{-- ✅ This layout includes toasts --}}
```

**Check 3:** Are you using the correct session keys?

```php
// ✅ Correct
->with('success', 'Message')
->with('error', 'Message')
->with('warning', 'Message')
->with('info', 'Message')

// ❌ Wrong
->with('message', 'Message')  // Won't work
->with('status', 'Message')   // Won't work
```

### Problem: Duplicate Messages

If you see both a banner AND a toast, you have both systems included:

```blade
{{-- ❌ BAD - Don't include both --}}
@include('components.flash-messages')
@include('components.toast-notifications')

{{-- ✅ GOOD - Just use one (they both show toasts now) --}}
@include('components.toast-notifications')
```

### Problem: Toasts Overlap Sidebar

Toasts are positioned at `top: 80px; right: 20px;` to clear the topbar. If you have a custom layout, adjust the `top` value in the CSS.

---

## 📊 Validation Errors

Validation errors are **automatically converted to toasts**:

```php
// Single error
$request->validate([
    'name' => 'required|max:255'
]);
// Shows: "Error | The name field is required."

// Multiple errors
$request->validate([
    'name' => 'required',
    'email' => 'required|email',
    'phone' => 'required|numeric'
]);
// Shows: "Error | The name field is required.<br>The email must be a valid email address.<br>..."
```

---

## 🎬 Visual Examples

### Success Toast (Green)
```
┌─────────────────────────────────┐
│ ✓  SUCCESS                      │ [×]
│    Activity created successfully│
└─────────────────────────────────┘
   ▓▓▓▓▓▓░░░░░░░░░░░░░░  (progress bar)
```

### Error Toast (Red)
```
┌─────────────────────────────────┐
│ ⚠  ERROR                        │ [×]
│    Failed to save changes       │
└─────────────────────────────────┘
   ▓▓▓▓▓▓░░░░░░░░░░░░░░  (progress bar)
```

### Warning Toast (Yellow)
```
┌─────────────────────────────────┐
│ ⚠  WARNING                      │ [×]
│    Warranty expires in 30 days  │
└─────────────────────────────────┘
   ▓▓▓▓▓▓░░░░░░░░░░░░░░  (progress bar)
```

---

## 📚 Additional Resources

- **Component File:** `resources/views/components/toast-notifications.blade.php`
- **Layout Integration:** `resources/views/layouts/app.blade.php` (Line 1317)
- **Backward Compatibility:** `resources/views/components/flash-messages.blade.php`

---

## ✨ Summary

**The toast notification system is now the STANDARD way to show user feedback in CREAMS.**

- Controllers: Use `->with('success', 'message')`
- JavaScript: Use `ToastNotification.success('message')`
- Views: Already included via `layouts/app.blade.php`

**Questions?** Check this documentation or contact the development team.

---

**End of Documentation**
