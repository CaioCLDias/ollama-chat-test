#!/bin/bash

echo "🔧 Fixing storage and cache directory permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Starting Laravel setup..."

# Laravel setup
php artisan migrate --force
php artisan db:seed --force
php artisan l5-swagger:generate
php artisan users:process-user-deletions &
php artisan chat:update-main-message &

# Start the scheduler in the background
php artisan schedule:work >> storage/logs/schedule.log 2>&1 &

# Check if Ollama is responding
echo "🚀 Testing Ollama API via HTTP..."
curl -X POST http://ollama:11434/api/generate \
  -H "Content-Type: application/json" \
  -d '{"model": "llama3.2:1b", "prompt": "ready"}' || true

# Start PHP-FPM (required to keep the container alive)
php-fpm
