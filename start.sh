#!/bin/bash

echo "=== LangLearn API Startup ==="

# Create Firebase credentials from environment variable
echo "Creating Firebase credentials..."
mkdir -p storage/firebase

if [ -n "$FIREBASE_CREDENTIALS_JSON" ]; then
    echo "Using FIREBASE_CREDENTIALS_JSON from environment"
    echo "$FIREBASE_CREDENTIALS_JSON" > storage/firebase/firebase_credentials.json
else
    echo "WARNING: FIREBASE_CREDENTIALS_JSON not found in environment"
fi

# Set the Firebase credentials path for Laravel config
export FIREBASE_CREDENTIALS="/app/storage/firebase/firebase_credentials.json"
echo "Set FIREBASE_CREDENTIALS to: $FIREBASE_CREDENTIALS"

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from environment variables..."
    cat > .env << EOF
APP_NAME=LangLearn
APP_ENV=production
APP_KEY=
APP_DEBUG=false
LOG_LEVEL=error
DB_CONNECTION=sqlite
DB_DATABASE=/app/database/database.sqlite
FIREBASE_PROJECT_ID=langlearn-a53cd
FIREBASE_CREDENTIALS=/app/storage/firebase/firebase_credentials.json
EOF
fi

# Ensure database file exists FIRST
echo "Ensuring database file exists..."
mkdir -p database
touch database/database.sqlite

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