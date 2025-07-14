#!/bin/bash

# Deployment script for setting up avatar storage in CREAMS system
# Run this script on a Linux/Unix system after deploying the application

# Create storage directories
echo "Creating storage directories..."
mkdir -p storage/app/public/avatars

# Set proper permissions
echo "Setting permissions..."
chmod -R 775 storage/app/public
chmod -R 775 storage

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Clear Laravel caches
echo "Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Verify storage path exists and is writable
if [ -d "public/storage" ]; then
  echo "Storage link created successfully."
else
  echo "WARNING: Storage link was not created. Please run 'php artisan storage:link' manually."
fi

if [ -w "storage/app/public/avatars" ]; then
  echo "Avatar directory is writable."
else
  echo "WARNING: Avatar directory is not writable. Please check permissions."
  echo "Run: chmod -R 775 storage/app/public"
fi

echo "Setup complete!"