#!/bin/bash

# Docker Development Script for Monthli
# Usage: ./docker-dev.sh [command]

set -e

case "$1" in
    "up")
        echo "🚀 Starting Monthli development environment..."
        cp .env.docker .env
        docker-compose up -d
        echo "✅ Environment started! Visit http://localhost:8000"
        echo "📊 Services running:"
        docker-compose ps
        ;;
    "down")
        echo "🛑 Stopping Monthli development environment..."
        docker-compose down
        echo "✅ Environment stopped!"
        ;;
    "build")
        echo "🔨 Building Docker images..."
        docker-compose build --no-cache
        echo "✅ Build complete!"
        ;;
    "logs")
        echo "📋 Showing logs..."
        docker-compose logs -f
        ;;
    "shell")
        echo "🐚 Opening shell in app container..."
        docker-compose exec app bash
        ;;
    "composer")
        echo "📦 Running composer install..."
        docker-compose exec app composer install
        ;;
    "artisan")
        shift
        echo "🎨 Running artisan command: $@"
        docker-compose exec app php artisan "$@"
        ;;
    "migrate")
        echo "🗄️ Running migrations..."
        docker-compose exec app php artisan migrate
        ;;
    "seed")
        echo "🌱 Running seeders..."
        docker-compose exec app php artisan db:seed
        ;;
    "fresh")
        echo "🔄 Fresh migration with seed..."
        docker-compose exec app php artisan migrate:fresh --seed
        ;;
    "test")
        echo "🧪 Running tests..."
        docker-compose exec app php artisan test
        ;;
    "npm")
        shift
        echo "📦 Running npm command: $@"
        docker-compose exec node npm "$@"
        ;;
    "status")
        echo "📊 Container status:"
        docker-compose ps
        ;;
    "restart")
        echo "🔄 Restarting services..."
        docker-compose restart
        echo "✅ Services restarted!"
        ;;
    *)
        echo "🐳 Monthli Docker Development Helper"
        echo ""
        echo "Available commands:"
        echo "  up        - Start the development environment"
        echo "  down      - Stop the development environment"
        echo "  build     - Build Docker images"
        echo "  logs      - Show container logs"
        echo "  shell     - Open shell in app container"
        echo "  composer  - Run composer install"
        echo "  artisan   - Run artisan commands"
        echo "  migrate   - Run database migrations"
        echo "  seed      - Run database seeders"
        echo "  fresh     - Fresh migration with seed"
        echo "  test      - Run tests"
        echo "  npm       - Run npm commands"
        echo "  status    - Show container status"
        echo "  restart   - Restart all services"
        echo ""
        echo "Examples:"
        echo "  ./docker-dev.sh up"
        echo "  ./docker-dev.sh artisan make:model Post"
        echo "  ./docker-dev.sh npm run dev"
        ;;
esac
