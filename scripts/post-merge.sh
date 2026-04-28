#!/usr/bin/env bash
# ============================================================
# QuickSMS - Post-Merge Setup
# ============================================================
# Runs automatically after a task agent's branch is merged into
# main. Idempotent and non-interactive.
#
# Responsibilities:
#   1. Sync Composer deps and refresh the autoloader (catches
#      newly added classes such as App\Models\Partner).
#   2. Apply any new database migrations.
#   3. Clear Laravel's compiled caches so removed routes/views
#      do not stick around.
#
# Intentionally does NOT touch .env, APP_KEY, storage symlinks
# or seeders -- those are bootstrap concerns owned by setup.sh.
# ============================================================

set -euo pipefail

echo "[post-merge] composer install (sync + autoload refresh)"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "[post-merge] composer dump-autoload (belt-and-braces for new classes)"
composer dump-autoload --optimize --no-scripts

echo "[post-merge] php artisan migrate --force"
php artisan migrate --force

echo "[post-merge] clearing config/route/view caches"
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "[post-merge] done"
