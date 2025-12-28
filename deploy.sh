#!/bin/bash

# OfisiLink Deployment Script for cPanel/SSH
# Run this script after cloning/pulling from GitHub

set -e  # Exit on error

echo "🚀 Starting OfisiLink Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Are you in the Laravel root directory?${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Laravel directory detected${NC}"

# Step 1: Install/Update Composer Dependencies
echo -e "\n${YELLOW}📦 Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Step 2: Check/Create .env file
if [ ! -f ".env" ]; then
    echo -e "\n${YELLOW}📝 Creating .env file from .env.example...${NC}"
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ .env file created. Please configure it!${NC}"
    else
        echo -e "${RED}❌ Error: .env.example not found${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✓ .env file exists${NC}"
fi

# Step 3: Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "\n${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate
else
    echo -e "${GREEN}✓ APP_KEY already set${NC}"
fi

# Step 4: Set permissions
echo -e "\n${YELLOW}📁 Setting file permissions...${NC}"
chmod -R 755 storage bootstrap/cache 2>/dev/null || true
chmod -R 755 public 2>/dev/null || true
echo -e "${GREEN}✓ Permissions set${NC}"

# Step 5: Clear caches
echo -e "\n${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

# Step 6: Cache configuration (optional - uncomment if needed)
echo -e "\n${YELLOW}⚡ Optimizing application...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Application optimized${NC}"

# Step 7: Create storage link
echo -e "\n${YELLOW}🔗 Creating storage link...${NC}"
php artisan storage:link 2>/dev/null || echo -e "${YELLOW}⚠ Storage link may already exist${NC}"

# Step 8: Run migrations (commented out for safety - uncomment if needed)
# echo -e "\n${YELLOW}🗄️  Running database migrations...${NC}"
# php artisan migrate --force
# echo -e "${GREEN}✓ Migrations completed${NC}"

echo -e "\n${GREEN}✅ Deployment completed successfully!${NC}"
echo -e "\n${YELLOW}📋 Next steps:${NC}"
echo "1. Configure your .env file with database and other settings"
echo "2. Run migrations: php artisan migrate --force"
echo "3. Verify file permissions: storage and bootstrap/cache should be 755"
echo "4. Test your application in the browser"
echo -e "\n${GREEN}🎉 Happy deploying!${NC}"

