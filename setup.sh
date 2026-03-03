#!/bin/bash
# ============================================================
# QuickSMS Platform - Replit Setup Script
# ============================================================
# This script bootstraps the Laravel application after
# importing from Git into Replit. It handles:
#   - Composer dependency installation
#   - Environment configuration
#   - Application key generation
#   - Database migrations & seeding
#   - Storage symlink & permissions
# ============================================================

set -e

echo "========================================"
echo "  QuickSMS Platform - Setup"
echo "========================================"

# ---- 1. Install Composer Dependencies ----
echo ""
echo "[1/6] Installing Composer dependencies..."
if [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
    echo "  -> Composer install complete."
else
    echo "  -> vendor/ already exists, running composer install to sync..."
    composer install --no-interaction --optimize-autoloader
fi

# ---- 2. Environment File ----
echo ""
echo "[2/6] Setting up environment file..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "  -> Copied .env.example to .env"

    # Apply Replit-specific overrides
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env
    sed -i 's/^DB_HOST=.*/DB_HOST=helium/' .env
    sed -i 's/^DB_PORT=.*/DB_PORT=5432/' .env
    sed -i 's/^DB_DATABASE=.*/DB_DATABASE=heliumdb/' .env
    sed -i 's/^DB_USERNAME=.*/DB_USERNAME=postgres/' .env
    sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=password/' .env
    sed -i 's/^APP_NAME=.*/APP_NAME=QuickSMS/' .env
    sed -i 's|^APP_URL=.*|APP_URL=http://0.0.0.0:5000|' .env

    echo "  -> Applied Replit database & app settings"
else
    echo "  -> .env already exists, skipping."
fi

# ---- 3. Application Key ----
echo ""
echo "[3/6] Generating application key..."
if grep -q "^APP_KEY=$" .env 2>/dev/null || grep -q "^APP_KEY=base64:$" .env 2>/dev/null; then
    php artisan key:generate --force
    echo "  -> Application key generated."
else
    echo "  -> Application key already set, skipping."
fi

# ---- 4. Storage & Cache ----
echo ""
echo "[4/6] Setting up storage and cache..."
php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo "  -> Storage linked, directories created, caches cleared."

# ---- 5. Database Migrations ----
echo ""
echo "[5/6] Running database migrations..."
# Wait for PostgreSQL to be ready (Replit may still be starting it)
MAX_RETRIES=15
RETRY_COUNT=0
until php artisan db:monitor --databases=pgsql 2>/dev/null || [ $RETRY_COUNT -ge $MAX_RETRIES ]; do
    echo "  -> Waiting for PostgreSQL to be ready... (attempt $((RETRY_COUNT+1))/$MAX_RETRIES)"
    sleep 2
    RETRY_COUNT=$((RETRY_COUNT+1))
done

php artisan migrate --force
echo "  -> Migrations complete."

# ---- 6. Database Seeding ----
echo ""
echo "[6/6] Seeding database..."
php artisan db:seed --force
echo "  -> Seeding complete."

echo ""
echo "========================================"
echo "  Setup Complete!"
echo "========================================"
echo ""
echo "  Your QuickSMS platform is ready."
echo "  Click 'Run' to start the Laravel server."
echo "  The app will be available on port 5000."
echo ""
