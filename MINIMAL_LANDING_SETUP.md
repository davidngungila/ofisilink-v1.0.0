# Minimal Landing Page Setup for ofisilink.com

## Overview

This guide shows how to create a **minimal Laravel installation** that serves **only the landing page** on `ofisilink.com`. The full application will be hosted on separate domains (`live.ofisilink.com` and `demo.ofisilink.com`).

---

## Architecture

```
ofisilink.com          → Landing Page Only (Minimal Laravel)
live.ofisilink.com     → Full Application (Complete Laravel System)
demo.ofisilink.com     → Full Application (Complete Laravel System)
```

---

## Step 1: Simplify Routes

Replace `routes/web.php` with this minimal version:

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
    
    // If on subdomain, redirect to the respective domain
    if ($subdomain === 'live') {
        return redirect('https://live.ofisilink.com/login');
    }
    
    if ($subdomain === 'demo') {
        return redirect('https://demo.ofisilink.com/login');
    }
    
    // Show landing page only on root domain (ofisilink.com)
    return view('landing');
})->name('landing');

// Catch-all route for any other paths on main domain
Route::fallback(function () {
    return redirect('/');
});
```

---

## Step 2: Files to KEEP (Required)

### Essential Laravel Files
```
├── app/
│   ├── Http/
│   │   └── Kernel.php (keep minimal middleware)
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   └── app.php (minimal config)
├── public/
│   ├── index.php
│   └── assets/ (keep only landing page assets - see below)
├── resources/
│   └── views/
│       └── landing.blade.php (YOUR LANDING PAGE)
├── routes/
│   └── web.php (simplified version above)
├── storage/
│   └── (keep structure, can be empty)
├── vendor/ (Laravel dependencies)
├── .env
├── composer.json
└── composer.lock
```

### Required Assets (in `public/assets/`)
```
public/assets/
├── vendor/
│   ├── css/
│   │   ├── core.css
│   │   └── theme-default.css
│   ├── fonts/
│   │   ├── boxicons.css
│   │   └── boxicons/ (all font files)
│   ├── js/
│   │   ├── bootstrap.js
│   │   └── menu.js
│   └── libs/
│       ├── jquery/
│       │   └── jquery.js
│       ├── popper/
│       │   └── popper.js
│       └── perfect-scrollbar/
│           ├── perfect-scrollbar.css
│           └── perfect-scrollbar.js
├── css/
│   └── demo.css
└── img/
    ├── favicon/
    │   └── favicon.ico
    └── office_link_logo.png
```

---

## Step 3: Files to DELETE (Not Needed)

### Controllers (All)
```
app/Http/Controllers/ (DELETE ENTIRE FOLDER)
```

### Models (All)
```
app/Models/ (DELETE ENTIRE FOLDER)
```

### Middleware (Except Core)
```
app/Http/Middleware/ (DELETE ALL EXCEPT Kernel.php dependencies)
```

### Other Views
```
resources/views/ (DELETE ALL EXCEPT landing.blade.php)
```

### Database Files (Optional - if not using database)
```
database/ (DELETE ENTIRE FOLDER if not using database)
```

### Unused Assets
```
public/assets/ (DELETE everything not listed in "Required Assets" above)
```

---

## Step 4: Simplify Composer Dependencies

You can simplify `composer.json` to remove unnecessary packages:

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0"
    },
    "require-dev": {
        "laravel/pint": "^1.24"
    }
}
```

**Note:** Keep Laravel framework, but you can remove:
- `barryvdh/laravel-dompdf` (if not used)
- `spatie/laravel-permission` (if not used)
- `picqer/php-barcode-generator` (if not used)
- Other application-specific packages

---

## Step 5: Minimal .env Configuration

Your `.env` file only needs:

```env
APP_NAME=OfisiLink
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://ofisilink.com

LOG_CHANNEL=stack
LOG_LEVEL=error

# Database (optional - only if you need it for logging)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ofisilink_landing
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

---

## Step 6: Update Bootstrap

In `bootstrap/app.php`, you can simplify middleware:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Minimal middleware - no custom middleware needed
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## Step 7: Deployment Checklist

### On cPanel for ofisilink.com:

1. ✅ Upload only the minimal files listed above
2. ✅ Set document root to `public/`
3. ✅ Run `composer install --no-dev --optimize-autoloader`
4. ✅ Set `.env` file with correct `APP_URL`
5. ✅ Run `php artisan config:cache`
6. ✅ Run `php artisan route:cache`
7. ✅ Ensure `storage/` and `bootstrap/cache/` are writable

### Verify:
- ✅ `ofisilink.com` → Shows landing page
- ✅ `ofisilink.com/anything` → Redirects to `/`
- ✅ Subdomain redirects work (if configured in DNS)

---

## Step 8: Separate Application Domains

For `live.ofisilink.com` and `demo.ofisilink.com`:

1. **Deploy the FULL application** on separate servers/cPanel accounts
2. **Each domain has its own:**
   - Complete Laravel installation
   - Database
   - All controllers, models, views
   - All routes and functionality

3. **Update landing page links** to point to:
   - `https://live.ofisilink.com/login`
   - `https://demo.ofisilink.com/login`

---

## Benefits of This Setup

✅ **Lightweight** - Minimal Laravel installation, faster loading  
✅ **Secure** - No application code exposed on main domain  
✅ **Separated** - Landing page and application are independent  
✅ **Scalable** - Each domain can be scaled independently  
✅ **Maintainable** - Easy to update landing page without affecting application  

---

## Important Notes

1. **Database**: You may not need a database for the landing page. If you do, it's only for basic logging.

2. **Sessions**: The landing page doesn't need sessions, so you can disable session middleware.

3. **Cache**: Use route and config caching for better performance:
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

4. **Assets**: The landing page uses external images (Unsplash) and CDN resources, so local asset dependencies are minimal.

5. **Updates**: When updating the landing page, you only need to update `resources/views/landing.blade.php` and any assets it uses.

---

## Testing

After setup, test:

1. ✅ `ofisilink.com` → Landing page loads
2. ✅ `ofisilink.com/` → Landing page loads
3. ✅ `ofisilink.com/any-path` → Redirects to `/`
4. ✅ All assets load correctly (CSS, JS, images)
5. ✅ External resources load (fonts, YouTube videos)
6. ✅ Links to `live.ofisilink.com` and `demo.ofisilink.com` work

---

## Rollback Plan

If you need to rollback:

1. Keep a backup of the full system
2. The landing page is independent, so you can restore the full system anytime
3. Both can coexist - you can have the full system but only use the landing route

---

## Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server error logs
3. Verify `.env` configuration
4. Clear cache: `php artisan cache:clear && php artisan config:clear`

