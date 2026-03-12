#!/bin/bash
# fix_portals.sh - run this on the server

echo "--- Starting Best Solution Fixer ---"

PORTALS=("bs_abuja" "bs_kaduna" "bs_kd")

for portal in "${PORTALS[@]}"; do
    echo "Processing $portal..."
    target="/home/mienebis/schools/$portal"
    
    if [ -d "$target" ]; then
        # 1. Fix Permissions
        chmod -R 775 "$target/storage" "$target/bootstrap/cache"
        
        # 2. Clear Caches
        cd "$target"
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        
        echo "✅ $portal Fixed."
    else
        echo "❌ $portal not found at $target"
    fi
done

echo "--- Checking Abuja Logs ---"
tail -n 10 /home/mienebis/schools/bs_abuja/storage/logs/laravel.log

echo "--- Check PHP Version ---"
php -v
