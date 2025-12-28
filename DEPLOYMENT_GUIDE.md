# OfisiLink Deployment Guide for cPanel

This guide covers deploying your Laravel application to cPanel hosting.

## Prerequisites

1. **SSH Access** (recommended) - Contact your hosting provider to enable SSH access
2. **Git** installed on the server (usually available via cPanel's Terminal)
3. **Composer** installed on the server
4. **PHP 8.2+** (check via cPanel's PHP Selector)
5. **MySQL/MariaDB** database created via cPanel

## Deployment Methods

### Method 1: SSH Deployment (Recommended)

If you have SSH access, this is the fastest and most reliable method.

#### Step 1: SSH into your server

```bash
ssh username@omega.kilihost.com
# Or use the port if specified by your hosting provider
```

#### Step 2: Navigate to your document root

```bash
cd ~/public_html
# Or cd ~/domains/yourdomain.com/public_html (depending on cPanel setup)
```

#### Step 3: Clone or pull from GitHub

**If deploying for the first time:**
```bash
git clone https://github.com/davidngungila/ofisilink-v1.0.0.git .
```

**If updating existing deployment:**
```bash
git pull origin main
```

#### Step 4: Run deployment script

```bash
bash deploy.sh
```

Or manually run these commands:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file if it doesn't exist
cp .env.example .env

# Generate application key
php artisan key:generate

# Set proper permissions
chmod -R 755 storage bootstrap/cache
chown -R your_username:your_group storage bootstrap/cache

# Run migrations (optional - backup database first!)
# php artisan migrate --force

# Clear and cache configuration
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear application cache
php artisan cache:clear
```

---

### Method 2: cPanel Git Version Control

Many cPanel installations include Git integration.

#### Step 1: Access Git Version Control in cPanel

1. Log into cPanel
2. Find "Git Version Control" or "Git™ Version Control"
3. Click "Create" or "Manage"

#### Step 2: Clone Repository

1. Click "Create"
2. Repository URL: `https://github.com/davidngungila/ofisilink-v1.0.0.git`
3. Repository Path: `/home/username/public_html` (or your document root)
4. Branch: `main`
5. Click "Create"

#### Step 3: Deploy via Terminal

After cloning, use cPanel's Terminal to run the deployment commands (see Method 1, Step 4).

---

### Method 3: Manual Upload via FTP/cPanel File Manager

**Use this method only if SSH is not available.**

#### Step 1: Prepare files locally

```bash
# Run this on your local machine
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

#### Step 2: Upload files

Upload all files **except**:
- `.git/` folder
- `node_modules/` folder
- `.env` file (you'll create this on the server)
- `storage/logs/*` (keep the folder, but not log files)
- `vendor/` (install via Composer on server)

#### Step 3: Configure on server

1. Create `.env` file via cPanel File Manager
2. Run deployment commands via cPanel Terminal (if available)

---

## Important cPanel Configuration

### 1. Set Document Root to `public/` Directory

**Option A: Via cPanel Domain Settings**

1. Go to "Domains" or "Subdomains"
2. Edit your domain
3. Set Document Root to: `public_html/public` (or `public_html/yourdomain.com/public`)
4. Save

**Option B: Via `.htaccess` in public_html**

Create `.htaccess` in `public_html/`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 2. PHP Version

1. Go to "Select PHP Version" or "MultiPHP Manager"
2. Select PHP 8.2 or higher
3. Enable required extensions:
   - `mbstring`
   - `openssl`
   - `pdo`
   - `pdo_mysql`
   - `tokenizer`
   - `xml`
   - `json`
   - `bcmath`
   - `gd` (for image processing)
   - `zip`
   - `fileinfo`

### 3. Database Configuration

1. Create database via "MySQL Databases"
2. Create database user and assign privileges
3. Update `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### 4. Environment File (.env)

Create `.env` file in the root directory with these minimum settings:

```env
APP_NAME=OfisiLink
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Generate APP_KEY:**
```bash
php artisan key:generate
```

### 5. File Permissions

Set proper permissions (via Terminal or File Manager):

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
```

Or via cPanel File Manager:
- Right-click `storage` folder → Change Permissions → 755
- Right-click `bootstrap/cache` folder → Change Permissions → 755

### 6. Storage Link

Create symbolic link for storage:

```bash
php artisan storage:link
```

---

## Post-Deployment Checklist

- [ ] `.env` file created and configured
- [ ] `APP_KEY` generated
- [ ] Database configured and connected
- [ ] Document root set to `public/` directory
- [ ] PHP version set to 8.2+
- [ ] Required PHP extensions enabled
- [ ] File permissions set correctly (storage: 755)
- [ ] Storage link created (`php artisan storage:link`)
- [ ] Config cached (`php artisan config:cache`)
- [ ] Routes cached (`php artisan route:cache`)
- [ ] Views cached (`php artisan view:cache`)
- [ ] Database migrations run (if needed)
- [ ] Test the application in browser

---

## Updating Deployment

### Via SSH:

```bash
cd ~/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force  # Only if there are new migrations
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Via cPanel Git Version Control:

1. Go to Git Version Control in cPanel
2. Click "Pull or Deploy"
3. Select your repository
4. Click "Update from Remote"

Then run deployment commands via Terminal.

---

## Troubleshooting

### 500 Internal Server Error

1. Check `.env` file exists and `APP_KEY` is set
2. Check file permissions (`storage` and `bootstrap/cache` should be 755)
3. Check error logs: `storage/logs/laravel.log`
4. Enable debug temporarily in `.env`: `APP_DEBUG=true` (disable after fixing!)

### White Screen / Blank Page

1. Check PHP version (must be 8.2+)
2. Check error logs: `storage/logs/laravel.log`
3. Check if `vendor/` folder exists
4. Run `composer install` again

### Database Connection Error

1. Verify database credentials in `.env`
2. Check database user has proper privileges
3. Verify database exists
4. Check if database host is `localhost` (not `127.0.0.1` in some cPanel setups)

### Permission Denied Errors

```bash
chmod -R 755 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

Replace `username` with your cPanel username.

---

## Security Recommendations

1. **Never commit `.env` file** to Git (it's already in `.gitignore`)
2. Set `APP_DEBUG=false` in production
3. Use strong `APP_KEY`
4. Use HTTPS (SSL certificate)
5. Set proper file permissions
6. Regularly update dependencies: `composer update`
7. Keep Laravel updated
8. Use strong database passwords

---

## Support

For deployment issues:
1. Check `storage/logs/laravel.log` for errors
2. Check cPanel error logs
3. Verify PHP version and extensions
4. Verify file permissions

