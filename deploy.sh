#!/bin/bash

# CREAMS Production Deployment Script
# Version: 1.0
# Description: Deploys CREAMS to production environment

set -e  # Exit on any error

echo "🚀 Starting CREAMS Production Deployment..."
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if .env file exists
if [ ! -f .env ]; then
    print_error ".env file not found! Please create one from .env.example"
    exit 1
fi

# 1. Update dependencies
print_status "Installing/updating PHP dependencies..."
composer install --no-dev --optimize-autoloader

# 2. Install Node dependencies and build assets
print_status "Installing Node dependencies and building assets..."
npm ci
npm run build

# 3. Clear and optimize Laravel caches
print_status "Clearing and optimizing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Run database migrations
print_status "Running database migrations..."
php artisan migrate --force

# 5. Seed database if needed (ask user)
read -p "Do you want to run database seeders? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Running database seeders..."
    php artisan db:seed --force
fi

# 6. Create storage link
print_status "Creating storage symbolic link..."
php artisan storage:link

# 7. Optimize for production
print_status "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Set proper file permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# 9. Create directory for logs if it doesn't exist
mkdir -p storage/logs

# 10. Run a quick health check
print_status "Running system health check..."
php artisan about

# 11. Optional: Run tests
read -p "Do you want to run tests? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    print_status "Running tests..."
    php artisan test
fi

# 12. Final checks
print_status "Performing final checks..."

# Check if key environment variables are set
if grep -q "APP_KEY=base64:" .env; then
    print_status "✓ Application key is set"
else
    print_warning "⚠ Application key may not be properly set"
fi

if grep -q "APP_ENV=production" .env; then
    print_status "✓ Environment set to production"
else
    print_warning "⚠ Environment is not set to production"
fi

if grep -q "APP_DEBUG=false" .env; then
    print_status "✓ Debug mode disabled"
else
    print_warning "⚠ Debug mode is enabled (not recommended for production)"
fi

# Check database connection
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" > /dev/null 2>&1; then
    print_status "✓ Database connection successful"
else
    print_error "✗ Database connection failed"
fi

echo "================================================"
print_status "🎉 CREAMS deployment completed successfully!"
echo ""
print_status "Next steps:"
echo "  1. Configure your web server to point to the 'public' directory"
echo "  2. Set up SSL certificate for HTTPS"
echo "  3. Configure backup strategies"
echo "  4. Set up monitoring and logging"
echo "  5. Test the application thoroughly"
echo ""
print_warning "Important reminders:"
echo "  - Ensure APP_ENV=production in .env"
echo "  - Ensure APP_DEBUG=false in .env"
echo "  - Set up proper database backups"
echo "  - Configure email settings for production"
echo "  - Set up cron jobs for Laravel scheduler if needed"
echo ""
print_status "Access your application at: $(grep APP_URL .env | cut -d '=' -f2)"
echo "================================================"