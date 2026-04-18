#!/bin/bash

# AZ-305 Helper - Azure App Service Startup Script

set -e

echo "Starting AZ-305 Helper application..."

# Set permissions
echo "Setting directory permissions..."
chmod -R 755 /home/site/wwwroot/data
chmod -R 755 /home/site/wwwroot/data/sessions

# Create sessions directory if it doesn't exist
mkdir -p /home/site/wwwroot/data/sessions
chmod 755 /home/site/wwwroot/data/sessions

# Log startup information
echo "Application started at $(date)"
echo "PHP version: $(php -v)"
echo "Environment: ${APP_ENV:-production}"

# Start Apache
exec apache2-foreground
