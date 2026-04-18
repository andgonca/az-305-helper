#!/bin/bash

# AZ-305 Helper - Linux/Mac Quick Start Script

echo ""
echo "========================================"
echo "AZ-305 Certification Helper"
echo "Quick Start Script (Linux/Mac)"
echo "========================================"
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "[ERROR] PHP is not installed."
    echo ""
    echo "Install PHP 8.2 or higher:"
    echo "  macOS:  brew install php@8.2"
    echo "  Ubuntu: sudo apt-get install php8.2 php8.2-cli"
    echo "  CentOS: sudo yum install php82"
    echo ""
    exit 1
fi

echo "[OK] PHP is installed"
php -v | grep -i "php"
echo ""

# Check if Git is installed
if ! command -v git &> /dev/null; then
    echo "[WARNING] Git is not installed. Version control may not work."
    echo "Install Git:"
    echo "  macOS:  brew install git"
    echo "  Ubuntu: sudo apt-get install git"
    echo "  CentOS: sudo yum install git"
    echo ""
else
    echo "[OK] Git is installed"
    git --version
    echo ""
fi

# Create necessary directories
echo "[*] Creating directory structure..."
mkdir -p data/sessions
echo "[OK] Created data/sessions directory"

# Set permissions
echo "[*] Setting file permissions..."
chmod -R 755 data
chmod -R 755 data/sessions
echo "[OK] Set permissions (755) for data directories"

echo ""
echo "========================================"
echo "Starting Development Server"
echo "========================================"
echo ""
echo "Your application will be available at:"
echo "  http://localhost:8000"
echo ""
echo "To stop the server, press Ctrl+C"
echo ""

# Change to public directory and start server
cd public
php -S localhost:8000
