#!/bin/bash

echo "=== LangLearn API Startup ==="

# Create Firebase credentials from environment variable
echo "Creating Firebase credentials..."
mkdir -p storage/firebase
echo "$FIREBASE_CREDENTIALS_JSON" > storage/firebase/firebase_credentials.json

# Generate application key
echo "Generating application key..."
php artisan key:generate --force

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database
echo "Seeding database..."
php artisan db:seed --force

# Start Laravel server
echo "Starting Laravel server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT