# Landing Page Setup Guide

## Current Configuration

The landing page is **already configured** to show only on `ofisilink.com`. Here's how it works:

### Domain Routing (Already Set Up)

In `routes/web.php`, the root route (`/`) checks for subdomains:

```php
Route::get('/', function () {
    $subdomain = getSubdomain();
    
    // If on subdomain, redirect to login page
    if ($subdomain === 'live') {
        return redirect()->route('login');
    }
    
    if ($subdomain === 'demo') {
        return redirect()->route('login');
    }
    
    // Show landing page only on root domain (ofisilink.com)
    return view('landing');
})->name('landing');
```

**Result:**
- ✅ `ofisilink.com` → Shows landing page
- ✅ `live.ofisilink.com` → Redirects to login
- ✅ `demo.ofisilink.com` → Redirects to login

---

## Landing Page Dependencies

The landing page (`resources/views/landing.blade.php`) is a **standalone HTML file** that doesn't extend other layouts, but it **does depend on** the following assets:

### Required Assets (MUST KEEP)

#### 1. CSS Files
```
public/assets/vendor/css/core.css
public/assets/vendor/css/theme-default.css
public/assets/css/demo.css
public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css
```

#### 2. JavaScript Files
```
public/assets/vendor/libs/jquery/jquery.js
public/assets/vendor/libs/popper/popper.js
public/assets/vendor/js/bootstrap.js
public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js
public/assets/vendor/js/menu.js
public/assets/js/main.js
```

#### 3. Fonts & Icons
```
public/assets/vendor/fonts/boxicons.css
public/assets/vendor/fonts/boxicons/ (entire folder with font files)
```

#### 4. Images
```
public/assets/img/favicon/favicon.ico
public/assets/img/office_link_logo.png
public/assets/img/illustrations/man-with-laptop-light.png (optional, used as fallback)
```

#### 5. External Resources (CDN)
- Google Fonts (Public Sans) - loaded from CDN
- Bootstrap Icons (Boxicons) - loaded from CDN
- YouTube embeds (for video demo)

---

## What Can Be Safely Removed?

If you **only want the landing page** on `ofisilink.com` and don't need the application functionality, you can:

### ✅ Safe to Remove

1. **All Controllers** (except minimal routing)
   - `app/Http/Controllers/` (except `AuthController` if you want login redirects)

2. **All Models** (if not using database)
   - `app/Models/`

3. **All Middleware** (except basic ones)
   - `app/Http/Middleware/` (keep only essential Laravel middleware)

4. **All Application Views** (except landing page)
   - `resources/views/` (keep only `landing.blade.php`)

5. **Database Migrations** (if not using database)
   - `database/migrations/`

6. **All Routes** (except landing page route)
   - Most routes in `routes/web.php` can be removed

7. **Unused Assets**
   - Any images not used in landing page
   - Any JS/CSS files not referenced in landing page

### ❌ Must Keep

1. **Laravel Core Files**
   - `app/`, `bootstrap/`, `config/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`

2. **Required Assets** (listed above)

3. **Route File** (minimal version)
   - Keep the landing page route in `routes/web.php`

---

## Minimal Setup for Landing Page Only

If you want to create a **minimal setup** with only the landing page:

### Step 1: Simplify `routes/web.php`

```php
<?php
use Illuminate\Support\Facades\Route;

// Helper function to get subdomain
function getSubdomain() {
    $host = request()->getHost();
    $parts = explode('.', $host);
    
    if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
        return null;
    }
    
    if (count($parts) > 2) {
        return $parts[0];
    }
    
    return null;
}

// Landing page - only on root domain
Route::get('/', function () {
    $subdomain = getSubdomain();
    
    if ($subdomain === 'live' || $subdomain === 'demo') {
        // Redirect to external login or show message
        return redirect('https://' . $subdomain . '.ofisilink.com/login');
    }
    
    return view('landing');
})->name('landing');
```

### Step 2: Keep Only Required Assets

Keep only the assets listed in the "Required Assets" section above.

### Step 3: Remove Unused Files

You can remove:
- All controllers (except if needed for routing)
- All models
- All other views (except `landing.blade.php`)
- All other routes

---

## Recommendation

**For Production:**

1. **Keep the current setup** - It's already configured correctly
2. **The landing page is isolated** - It doesn't interfere with `live` or `demo` subdomains
3. **No need to delete other files** - They don't affect the landing page performance
4. **The application routes are protected** - They require authentication, so they won't be accessible from the main domain

**The landing page will work perfectly on `ofisilink.com` without any changes!**

---

## Testing

To test the setup:

1. **Main Domain**: Visit `ofisilink.com` → Should show landing page
2. **Live Subdomain**: Visit `live.ofisilink.com` → Should redirect to login
3. **Demo Subdomain**: Visit `demo.ofisilink.com` → Should redirect to login

---

## Notes

- The landing page uses **external images** (Unsplash) for most content, so it doesn't depend on your domain's images
- The landing page is **self-contained** - all styles are in the `<style>` tag within the file
- The landing page uses **CDN resources** for fonts and icons, reducing dependency on local files
- You can safely keep all application files - they won't affect the landing page since routes are separate

