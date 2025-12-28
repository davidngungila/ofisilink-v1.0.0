#!/bin/bash

# OfisiLink Quick Update Script
# Use this script to update an existing deployment

set -e

echo "🔄 Updating OfisiLink..."

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Are you in the Laravel root directory?${NC}"
    exit 1
fi

# Pull latest code
echo -e "\n${YELLOW}📥 Pulling latest code from GitHub...${NC}"
git pull origin main

# Install/Update dependencies
echo -e "\n${YELLOW}📦 Updating Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# Clear and rebuild caches
echo -e "\n${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "\n${YELLOW}⚡ Rebuilding caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (commented for safety)
# echo -e "\n${YELLOW}🗄️  Running database migrations...${NC}"
# php artisan migrate --force

# Set permissions
echo -e "\n${YELLOW}📁 Setting permissions...${NC}"
chmod -R 755 storage bootstrap/cache 2>/dev/null || true

echo -e "\n${GREEN}✅ Update completed successfully!${NC}"

