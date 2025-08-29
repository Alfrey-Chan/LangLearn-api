#!/bin/bash

# Railway deployment script for Laravel
echo "🚀 Starting Laravel deployment on Railway..."

# Install dependencies
composer install --no-dev --optimize-autoloader

# Create database directory if it doesn't exist
mkdir -p /app/database

# Create empty SQLite database
touch /app/database/database.sqlite

# Generate application key if not set
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Seed the database with initial data
php artisan db:seed --force

# Cache configuration and routes for performance
php artisan config:cache
php artisan route:cache

# Set proper permissions
chmod -R 755 /app/storage
chmod -R 755 /app/database

echo "✅ Laravel deployment completed!"