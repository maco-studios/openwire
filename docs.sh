#!/bin/bash

# Documentation development helper script

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if virtual environment exists
check_venv() {
    if [ ! -d "venv" ]; then
        print_status "Creating Python virtual environment..."
        python3 -m venv venv
        print_success "Virtual environment created"
    fi
}

# Install dependencies
install_deps() {
    print_status "Installing/updating Python dependencies..."
    ./venv/bin/pip install -r requirements.txt
    print_success "Dependencies installed"
}

# Serve documentation locally
serve_docs() {
    check_venv
    install_deps
    print_status "Starting documentation server..."
    print_status "Documentation will be available at: http://127.0.0.1:8000"
    print_status "Press Ctrl+C to stop the server"
    ./venv/bin/mkdocs serve
}

# Build documentation
build_docs() {
    check_venv
    install_deps
    print_status "Building documentation..."
    ./venv/bin/mkdocs build
    print_success "Documentation built successfully"
    print_status "Output directory: ./site/"
}

# Deploy to GitHub Pages
deploy_docs() {
    check_venv
    install_deps
    print_status "Deploying documentation to GitHub Pages..."
    ./venv/bin/mkdocs gh-deploy
    print_success "Documentation deployed to GitHub Pages"
}

# Clean build artifacts
clean() {
    print_status "Cleaning build artifacts..."
    rm -rf site/
    print_success "Build artifacts cleaned"
}

# Show help
show_help() {
    echo "OpenWire Documentation Helper"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  serve     Start local development server (default)"
    echo "  build     Build static documentation"
    echo "  deploy    Deploy to GitHub Pages"
    echo "  clean     Clean build artifacts"
    echo "  setup     Set up development environment"
    echo "  help      Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 serve    # Start development server"
    echo "  $0 build    # Build documentation"
    echo "  $0 deploy   # Deploy to GitHub Pages"
}

# Setup development environment
setup_env() {
    print_status "Setting up development environment..."
    check_venv
    install_deps
    print_success "Development environment ready"
    print_status "You can now run: $0 serve"
}

# Main command handling
case "${1:-serve}" in
    "serve")
        serve_docs
        ;;
    "build")
        build_docs
        ;;
    "deploy")
        deploy_docs
        ;;
    "clean")
        clean
        ;;
    "setup")
        setup_env
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        echo ""
        show_help
        exit 1
        ;;
esac
