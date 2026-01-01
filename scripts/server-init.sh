#!/bin/bash

# ============================================
# CREAMS Server Initialization Script
# Run this once on a fresh Lightsail server
# ============================================

set -e  # Exit on any error

echo "🚀 Starting CREAMS server initialization..."

# ============================================
# 1. System Updates
# ============================================
echo "📦 Updating system packages..."
sudo dnf update -y

# ============================================
# 2. Install PHP 8.1
# ============================================
echo "🐘 Installing PHP 8.1..."
sudo dnf install -y php8.1 php8.1-cli php8.1-fpm php8.1-mysqlnd \
    php8.1-pdo php8.1-xml php8.1-mbstring php8.1-curl \
    php8.1-zip php8.1-gd php8.1-bcmath php8.1-intl

# ============================================
# 3. Install MySQL
# ============================================
echo "🗄️ Installing MySQL..."
sudo dnf install -y mysql-server
sudo systemctl start mysqld
sudo systemctl enable mysqld

# ============================================
# 4. Install Nginx
# ============================================
echo "🌐 Installing Nginx..."
sudo dnf install -y nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# ============================================
# 5. Install Git & Node.js
# ============================================
echo "📚 Installing Git and Node.js..."
sudo dnf install -y git
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo dnf install -y nodejs

# ============================================
# 6. Install Composer
# ============================================
echo "🎼 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# ============================================
# 7. Create Directory Structure
# ============================================
echo "📁 Creating directory structure..."
sudo mkdir -p /var/www/portfolio
sudo mkdir -p /var/www/creams-prod
sudo mkdir -p /var/www/creams-staging
sudo mkdir -p /var/www/creams-dev

# Set ownership
sudo chown -R $USER:$USER /var/www

# ============================================
# 8. Create Portfolio Placeholder
# ============================================
echo "🏠 Creating portfolio placeholder..."
cat > /var/www/portfolio/index.html << 'PORTFOLIO_EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faisal Hanafi - Portfolio</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container { text-align: center; padding: 2rem; }
        h1 { font-size: 3rem; margin-bottom: 1rem; }
        p { font-size: 1.2rem; color: #a0a0a0; margin-bottom: 2rem; }
        .links { margin-top: 2rem; }
        .links a {
            color: #4fc3f7;
            text-decoration: none;
            padding: 0.8rem 1.5rem;
            border: 2px solid #4fc3f7;
            border-radius: 5px;
            margin: 0.5rem;
            display: inline-block;
            transition: all 0.3s;
        }
        .links a:hover {
            background: #4fc3f7;
            color: #1a1a2e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Faisal Hanafi</h1>
        <p>Software Developer | Portfolio Coming Soon</p>
        <div class="links">
            <a href="/creams/demo/">CREAMS Demo</a>
        </div>
    </div>
</body>
</html>
PORTFOLIO_EOF

# ============================================
# 9. Clone Repository for Each Environment
# ============================================
echo "📥 Cloning repositories..."

# Replace with your actual GitHub repo URL
REPO_URL="https://github.com/YOUR_USERNAME/CREAMS.git"

cd /var/www/creams-prod
git clone -b main $REPO_URL .

cd /var/www/creams-staging
git clone -b staging $REPO_URL .

cd /var/www/creams-dev
git clone -b dev $REPO_URL .

# ============================================
# 10. Create MySQL Databases
# ============================================
echo "🗄️ Creating databases..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS creams_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE DATABASE IF NOT EXISTS creams_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE DATABASE IF NOT EXISTS creams_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

sudo mysql -e "CREATE USER IF NOT EXISTS 'creams_prod'@'localhost' IDENTIFIED BY 'ProdPassword123!';"
sudo mysql -e "CREATE USER IF NOT EXISTS 'creams_staging'@'localhost' IDENTIFIED BY 'StagingPassword123!';"
sudo mysql -e "CREATE USER IF NOT EXISTS 'creams_dev'@'localhost' IDENTIFIED BY 'DevPassword123!';"

sudo mysql -e "GRANT ALL ON creams_prod.* TO 'creams_prod'@'localhost';"
sudo mysql -e "GRANT ALL ON creams_staging.* TO 'creams_staging'@'localhost';"
sudo mysql -e "GRANT ALL ON creams_dev.* TO 'creams_dev'@'localhost';"

sudo mysql -e "FLUSH PRIVILEGES;"

# ============================================
# 11. Setup Each Environment
# ============================================
setup_environment() {
    local env_name=$1
    local env_dir=$2
    local db_name=$3
    local db_user=$4
    local db_pass=$5
    local app_env=$6
    local app_url=$7
    local app_debug=$8

    echo "⚙️ Setting up $env_name environment..."

    cd $env_dir

    # Install dependencies
    composer install --no-dev --optimize-autoloader
    npm ci && npm run build

    # Create .env
    cat > .env << ENV_EOF
APP_NAME="CREAMS [$env_name]"
APP_ENV=$app_env
APP_KEY=
APP_DEBUG=$app_debug
APP_URL=$app_url
APP_TIMEZONE=Asia/Kuala_Lumpur

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$db_name
DB_USERNAME=$db_user
DB_PASSWORD=$db_pass

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=stack
LOG_LEVEL=debug
ENV_EOF

    # Generate key and run migrations
    php artisan key:generate
    php artisan migrate --force
    php artisan storage:link

    # Cache config
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
}

# Production
setup_environment "PROD" "/var/www/creams-prod" "creams_prod" "creams_prod" "ProdPassword123!" "production" "https://faisalhanafi.com" "false"

# Staging
setup_environment "STAGING" "/var/www/creams-staging" "creams_staging" "creams_staging" "StagingPassword123!" "staging" "https://staging.faisalhanafi.com" "true"

# Development
setup_environment "DEV" "/var/www/creams-dev" "creams_dev" "creams_dev" "DevPassword123!" "development" "https://dev.faisalhanafi.com" "true"

# ============================================
# 12. Set Permissions
# ============================================
echo "🔐 Setting permissions..."
sudo chown -R nginx:nginx /var/www/creams-prod
sudo chown -R nginx:nginx /var/www/creams-staging
sudo chown -R nginx:nginx /var/www/creams-dev

for dir in creams-prod creams-staging creams-dev; do
    sudo chmod -R 755 /var/www/$dir
    sudo chmod -R 775 /var/www/$dir/storage
    sudo chmod -R 775 /var/www/$dir/bootstrap/cache
done

# Allow ec2-user to manage files (for CI/CD)
sudo usermod -a -G nginx ec2-user

# ============================================
# 13. Install Certbot for SSL
# ============================================
echo "🔒 Installing Certbot..."
sudo dnf install -y certbot python3-certbot-nginx

echo ""
echo "============================================"
echo "✅ Server initialization complete!"
echo "============================================"
echo ""
echo "Next steps:"
echo "1. Copy nginx config to /etc/nginx/conf.d/"
echo "2. Run: sudo nginx -t && sudo systemctl reload nginx"
echo "3. Run: sudo certbot --nginx -d faisalhanafi.com -d www.faisalhanafi.com -d staging.faisalhanafi.com -d dev.faisalhanafi.com"
echo "4. Set up GitHub secrets for CI/CD"
echo ""
echo "URLs:"
echo "  Production:  https://faisalhanafi.com/creams/demo/"
echo "  Staging:     https://staging.faisalhanafi.com/"
echo "  Development: https://dev.faisalhanafi.com/"
echo ""
