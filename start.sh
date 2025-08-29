#!/bin/bash

echo "=== LangLearn API Startup ==="

# Create Firebase credentials from environment variable
echo "Creating Firebase credentials..."
mkdir -p storage/firebase
echo "$FIREBASE_CREDENTIALS_JSON" > storage/firebase/firebase_credentials.json

# Set the Firebase credentials path for Laravel config
export FIREBASE_CREDENTIALS="/app/storage/firebase/firebase_credentials.json"

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# Clear all caches and regenerate service providers
echo "Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
php artisan config:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database
echo "Seeding database..."
php artisan db:seed --force

# Start Laravel server
echo "Starting Laravel server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT