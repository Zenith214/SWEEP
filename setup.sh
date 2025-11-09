#!/bin/bash

################################################################################
# SWEEP - Setup Script for Linux/macOS
# This script automates the complete installation and setup process
################################################################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_header() {
    echo -e "\n${BLUE}═══════════════════════════════════════════════════════${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}═══════════════════════════════════════════════════════${NC}\n"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Function to check PHP version
check_php_version() {
    if command_exists php; then
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        PHP_MAJOR=$(php -r "echo PHP_MAJOR_VERSION;")
        PHP_MINOR=$(php -r "echo PHP_MINOR_VERSION;")
        
        if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
            print_success "PHP $PHP_VERSION detected"
            return 0
        else
            print_error "PHP 8.2+ required, found $PHP_VERSION"
            return 1
        fi
    else
        print_error "PHP not found"
        return 1
    fi
}

# Function to check Node.js version
check_node_version() {
    if command_exists node; then
        NODE_VERSION=$(node -v | cut -d'v' -f2)
        NODE_MAJOR=$(echo $NODE_VERSION | cut -d'.' -f1)
        
        if [ "$NODE_MAJOR" -ge 18 ]; then
            print_success "Node.js v$NODE_VERSION detected"
            return 0
        else
            print_error "Node.js 18+ required, found v$NODE_VERSION"
            return 1
        fi
    else
        print_error "Node.js not found"
        return 1
    fi
}

# Function to check required PHP extensions
check_php_extensions() {
    print_info "Checking PHP extensions..."
    
    REQUIRED_EXTENSIONS=(
        "bcmath"
        "ctype"
        "curl"
        "dom"
        "fileinfo"
        "filter"
        "gd"
        "hash"
        "mbstring"
        "openssl"
        "pcre"
        "pdo"
        "pdo_mysql"
        "session"
        "tokenizer"
        "xml"
        "zip"
    )
    
    MISSING_EXTENSIONS=()
    
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if php -m | grep -qi "^$ext$"; then
            print_success "$ext extension found"
        else
            print_error "$ext extension missing"
            MISSING_EXTENSIONS+=("$ext")
        fi
    done
    
    if [ ${#MISSING_EXTENSIONS[@]} -gt 0 ]; then
        print_error "Missing PHP extensions: ${MISSING_EXTENSIONS[*]}"
        print_info "Install missing extensions and run this script again"
        return 1
    fi
    
    return 0
}

# Main setup process
main() {
    print_header "SWEEP Installation Script"
    
    print_info "Starting system requirements check..."
    
    # Check PHP
    print_header "Checking PHP"
    if ! check_php_version; then
        print_error "Please install PHP 8.2 or higher"
        exit 1
    fi
    
    # Check PHP extensions
    if ! check_php_extensions; then
        exit 1
    fi
    
    # Check Composer
    print_header "Checking Composer"
    if command_exists composer; then
        COMPOSER_VERSION=$(composer --version | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_success "Composer $COMPOSER_VERSION detected"
    else
        print_error "Composer not found"
        print_info "Install Composer from https://getcomposer.org/"
        exit 1
    fi
    
    # Check Node.js
    print_header "Checking Node.js"
    if ! check_node_version; then
        print_error "Please install Node.js 18 or higher"
        exit 1
    fi
    
    # Check npm
    if command_exists npm; then
        NPM_VERSION=$(npm -v)
        print_success "npm $NPM_VERSION detected"
    else
        print_error "npm not found"
        exit 1
    fi
    
    # Check database
    print_header "Checking Database"
    if command_exists mysql; then
        MYSQL_VERSION=$(mysql --version | grep -oP '\d+\.\d+\.\d+' | head -1)
        print_success "MySQL/MariaDB $MYSQL_VERSION detected"
    else
        print_warning "MySQL/MariaDB not detected - make sure it's installed and running"
    fi
    
    print_success "All system requirements met!"
    
    # Install dependencies
    print_header "Installing PHP Dependencies"
    print_info "Running: composer install"
    composer install --no-interaction --prefer-dist --optimize-autoloader
    print_success "PHP dependencies installed"
    
    print_header "Installing JavaScript Dependencies"
    print_info "Running: npm install"
    npm install
    print_success "JavaScript dependencies installed"
    
    # Environment setup
    print_header "Setting Up Environment"
    if [ ! -f .env ]; then
        print_info "Creating .env file from .env.example"
        cp .env.example .env
        print_success ".env file created"
    else
        print_warning ".env file already exists, skipping"
    fi
    
    # Generate application key
    print_header "Generating Application Key"
    php artisan key:generate --ansi
    print_success "Application key generated"
    
    # Database setup
    print_header "Database Setup"
    read -p "Do you want to run database migrations now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Running migrations..."
        php artisan migrate --force
        print_success "Database migrations completed"
        
        read -p "Do you want to seed the database with sample data? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            print_info "Seeding database..."
            php artisan db:seed --force
            print_success "Database seeded"
            
            print_header "Default Login Credentials"
            echo -e "${GREEN}Administrator:${NC}"
            echo -e "  Email: admin@sweep.local"
            echo -e "  Password: ChangeMe123!"
            echo ""
            echo -e "${GREEN}Collection Crew:${NC}"
            echo -e "  Email: john.smith@sweep.local"
            echo -e "  Password: password"
            echo ""
            echo -e "${GREEN}Resident:${NC}"
            echo -e "  Email: john.doe@example.com"
            echo -e "  Password: password"
            echo ""
            print_warning "Change these passwords immediately in production!"
        fi
    else
        print_info "Skipping database setup"
        print_warning "Remember to run 'php artisan migrate' before using the application"
    fi
    
    # Storage link
    print_header "Creating Storage Link"
    php artisan storage:link
    print_success "Storage link created"
    
    # Set permissions
    print_header "Setting Permissions"
    chmod -R 775 storage bootstrap/cache
    print_success "Permissions set"
    
    # Build assets
    print_header "Building Frontend Assets"
    read -p "Do you want to build assets now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Building assets for production..."
        npm run build
        print_success "Assets built"
    else
        print_info "Skipping asset build"
        print_warning "Run 'npm run build' for production or 'npm run dev' for development"
    fi
    
    # Final message
    print_header "Installation Complete!"
    print_success "SWEEP has been successfully installed!"
    echo ""
    print_info "Next steps:"
    echo "  1. Configure your database in .env file"
    echo "  2. Run 'php artisan serve' to start the development server"
    echo "  3. Visit http://localhost:8000 in your browser"
    echo "  4. For development with hot reload: 'npm run dev' in another terminal"
    echo ""
    print_info "For production deployment, see README.md"
    echo ""
}

# Run main function
main
