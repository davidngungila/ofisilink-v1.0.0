# Quick Deployment Commands Reference

## First Time Deployment (SSH)

```bash
# 1. SSH into your server
ssh username@omega.kilihost.com

# 2. Navigate to document root
cd ~/public_html

# 3. Clone repository
git clone https://github.com/davidngungila/ofisilink-v1.0.0.git .

# 4. Make deploy script executable
chmod +x deploy.sh

# 5. Run deployment script
bash deploy.sh

# 6. Configure .env file
nano .env
# Edit with your database credentials, APP_URL, etc.

# 7. Generate APP_KEY (if not done by deploy.sh)
php artisan key:generate

# 8. Run migrations
php artisan migrate --force

# 9. Set permissions
chmod -R 755 storage bootstrap/cache
```

## Update Existing Deployment (SSH)

```bash
# 1. SSH into your server
ssh username@omega.kilihost.com

# 2. Navigate to project directory
cd ~/public_html

# 3. Pull latest code
git pull origin main

# 4. Run update script
chmod +x deploy-update.sh
bash deploy-update.sh

# OR manually:
composer install --no-dev --optimize-autoloader
php artisan migrate --force  # Only if there are new migrations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Essential Commands

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Cache for Performance
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database
```bash
# Run migrations
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status
```

### Storage Link
```bash
# Create symbolic link for storage
php artisan storage:link
```

### Permissions
```bash
# Set permissions (replace username with your cPanel username)
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

### View Logs
```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View last 100 lines
tail -n 100 storage/logs/laravel.log
```

### Check Application Status
```bash
# Check Laravel version
php artisan --version

# Check PHP version
php -v

# Check Composer version
composer --version

# Check if storage is writable
php artisan tinker
>>> File::isWritable(storage_path())
```

## Troubleshooting Commands

### Check Environment
```bash
# Display current environment
php artisan env

# Check if .env file exists
ls -la .env
```

### Rebuild Everything
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Check File Permissions
```bash
# Check storage permissions
ls -la storage/
ls -la bootstrap/cache/
```

### Test Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

## cPanel Terminal Access

1. Log into cPanel: https://omega.kilihost.com:2083
2. Find "Terminal" or "SSH Access"
3. Open Terminal
4. Navigate to your project: `cd public_html`
5. Run deployment commands

## File Upload via cPanel File Manager

If SSH is not available:

1. Log into cPanel
2. Go to "File Manager"
3. Navigate to `public_html`
4. Upload files (use "Compressed Archive" option for faster upload)
5. Extract archive on server
6. Use Terminal (if available) to run Composer and Artisan commands

---

**Note:** Always backup your database before running migrations in production!

