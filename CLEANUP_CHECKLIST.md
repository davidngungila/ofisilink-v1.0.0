# Cleanup Checklist for Minimal Landing Page Setup

Use this checklist to safely remove unnecessary files from your `ofisilink.com` installation.

## ⚠️ IMPORTANT: Backup First!

**Before starting, create a full backup of your current system!**

---

## Step-by-Step Cleanup

### ✅ Step 1: Backup Current System
- [ ] Create full backup of current installation
- [ ] Export database (if using one)
- [ ] Download all files via FTP/cPanel File Manager

### ✅ Step 2: Replace Routes File
- [ ] Backup current `routes/web.php`
- [ ] Replace with `routes/web.minimal.php` (rename it to `web.php`)
- [ ] Test that landing page still loads

### ✅ Step 3: Remove Controllers
- [ ] Delete `app/Http/Controllers/` folder (entire folder)
- [ ] Keep only `app/Http/Kernel.php` if it exists

### ✅ Step 4: Remove Models
- [ ] Delete `app/Models/` folder (entire folder)

### ✅ Step 5: Remove Middleware
- [ ] Delete custom middleware in `app/Http/Middleware/`
- [ ] Keep only Laravel core middleware files

### ✅ Step 6: Remove Other Views
- [ ] Keep only `resources/views/landing.blade.php`
- [ ] Delete all other `.blade.php` files
- [ ] Delete `resources/views/layouts/` folder
- [ ] Delete `resources/views/components/` folder
- [ ] Delete `resources/views/modules/` folder

### ✅ Step 7: Clean Up Assets
Keep only these assets:
- [ ] `public/assets/vendor/css/core.css`
- [ ] `public/assets/vendor/css/theme-default.css`
- [ ] `public/assets/css/demo.css`
- [ ] `public/assets/vendor/fonts/boxicons.css` and font files
- [ ] `public/assets/vendor/js/bootstrap.js`
- [ ] `public/assets/vendor/js/menu.js`
- [ ] `public/assets/vendor/libs/jquery/jquery.js`
- [ ] `public/assets/vendor/libs/popper/popper.js`
- [ ] `public/assets/vendor/libs/perfect-scrollbar/` (entire folder)
- [ ] `public/assets/img/favicon/favicon.ico`
- [ ] `public/assets/img/office_link_logo.png`
- [ ] `public/assets/js/main.js` (if used by landing page)

Delete everything else in `public/assets/`:
- [ ] Delete unused images
- [ ] Delete unused JavaScript files
- [ ] Delete unused CSS files
- [ ] Delete unused vendor libraries

### ✅ Step 8: Simplify Composer Dependencies
- [ ] Edit `composer.json`
- [ ] Remove unnecessary packages (see MINIMAL_LANDING_SETUP.md)
- [ ] Run `composer update --no-dev --optimize-autoloader`

### ✅ Step 9: Clean Database (Optional)
If you're not using a database:
- [ ] Delete `database/` folder
- [ ] Remove database configuration from `.env`

If you're keeping database for logging:
- [ ] Keep minimal database structure
- [ ] Remove all migrations except essential ones

### ✅ Step 10: Update .env
- [ ] Set `APP_URL=https://ofisilink.com`
- [ ] Set `APP_DEBUG=false`
- [ ] Remove or comment out unused service configurations

### ✅ Step 11: Clear and Cache
- [ ] Run `php artisan config:clear`
- [ ] Run `php artisan route:clear`
- [ ] Run `php artisan cache:clear`
- [ ] Run `php artisan view:clear`
- [ ] Then cache for production:
  - [ ] `php artisan config:cache`
  - [ ] `php artisan route:cache`

### ✅ Step 12: Test Landing Page
- [ ] Visit `ofisilink.com` → Should show landing page
- [ ] Check all CSS loads correctly
- [ ] Check all JavaScript loads correctly
- [ ] Check all images load correctly
- [ ] Test all links on landing page
- [ ] Test responsive design (mobile, tablet, desktop)
- [ ] Test video embeds work
- [ ] Test carousel/slider works
- [ ] Test all interactive elements

### ✅ Step 13: Test Redirects
- [ ] Visit `live.ofisilink.com` → Should redirect to `https://live.ofisilink.com/login`
- [ ] Visit `demo.ofisilink.com` → Should redirect to `https://demo.ofisilink.com/login`
- [ ] Visit `ofisilink.com/any-path` → Should redirect to `/`

### ✅ Step 14: Performance Check
- [ ] Check page load speed
- [ ] Verify no 404 errors for assets
- [ ] Check browser console for errors
- [ ] Test on different browsers

### ✅ Step 15: Security Check
- [ ] Verify `.env` is not publicly accessible
- [ ] Check file permissions (folders: 755, files: 644)
- [ ] Ensure `storage/` and `bootstrap/cache/` are writable
- [ ] Remove any test/debug files

---

## Files Structure After Cleanup

```
ofisi/
├── app/
│   ├── Http/
│   │   └── Kernel.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php
├── config/
│   └── app.php
├── public/
│   ├── index.php
│   └── assets/ (minimal - only landing page assets)
├── resources/
│   └── views/
│       └── landing.blade.php
├── routes/
│   └── web.php (minimal version)
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── vendor/ (Laravel dependencies)
├── .env
├── composer.json
└── composer.lock
```

---

## Rollback Instructions

If something goes wrong:

1. **Restore from backup**
2. **Revert `routes/web.php`** to original
3. **Restore deleted folders** from backup
4. **Run `composer install`** to restore dependencies
5. **Clear cache**: `php artisan config:clear && php artisan route:clear`

---

## Post-Cleanup Maintenance

### Regular Updates:
- [ ] Update Laravel framework: `composer update laravel/framework`
- [ ] Update landing page content as needed
- [ ] Update assets if landing page design changes

### Monitoring:
- [ ] Check error logs regularly: `storage/logs/laravel.log`
- [ ] Monitor page load times
- [ ] Check for broken links periodically

---

## Notes

- **Keep backups** of the full system in case you need to reference something
- **The landing page is independent** - you can always restore the full system
- **Test thoroughly** before going live with the minimal setup
- **Document any custom changes** you make during cleanup

