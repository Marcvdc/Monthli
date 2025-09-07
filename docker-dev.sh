#!/bin/bash

# Docker Development Script for Monthli
# Usage: ./docker-dev.sh [command]

set -e

case "$1" in
    "up")
        echo "🚀 Starting Monthli development environment..."
        # Handle .env file creation/update with proper permissions
        if [ ! -f .env ]; then
            cp .env.docker .env 2>/dev/null || cp .env.example .env
        elif [ -w .env ]; then
            cp .env.docker .env 2>/dev/null || echo "ℹ️  Using existing .env file"
        else
            echo "ℹ️  Using existing .env file (no write permissions)"
        fi
        cd docker && docker-compose up -d
        echo "✅ Environment started! Visit http://localhost:8000"
        echo "📊 Services running:"
        echo "Services are running on http://localhost:8000"
        ;;
    "down")
        echo "🛑 Stopping Monthli development environment..."
        cd docker && docker-compose down
        echo "✅ Environment stopped!"
        ;;
    "build")
        echo "🔨 Building Docker images..."
        cd docker && docker-compose build --no-cache
        echo "✅ Build complete!"
        ;;
    "logs")
        echo "📋 Showing logs..."
        cd docker && docker-compose logs -f
        ;;
    "shell")
        echo "🐚 Opening shell in app container..."
        cd docker && docker-compose exec app bash
        ;;
    "composer")
        echo "📦 Running composer install..."
        cd docker && docker-compose exec app composer install
        ;;
    "artisan")
        shift
        echo "🎨 Running artisan command: $@"
        cd docker && docker-compose exec app php artisan "$@"
        ;;
    "migrate")
        echo "🗄️ Running migrations..."
        cd docker && docker-compose exec app php artisan migrate
        ;;
    "seed")
        echo "🌱 Running seeders..."
        cd docker && docker-compose exec app php artisan db:seed
        ;;
    "fresh")
        echo "🔄 Fresh migration with seed..."
        cd docker && docker-compose exec app php artisan migrate:fresh --seed
        ;;
    "test")
        echo "🧪 Running tests..."
        cd docker && docker-compose exec app php artisan test
        ;;
    "npm")
        shift
        echo "📦 Running npm command: $@"
        cd docker && docker-compose exec node npm "$@"
        ;;
    "status")
        echo "📊 Container status:"
        cd docker && docker-compose ps
        ;;
    "restart")
        echo "🔄 Restarting services..."
        cd docker && docker-compose restart
        echo "✅ Services restarted!"
        ;;
    "setup")
        echo "🚀 Setting up Monthli development environment..."
        echo "This will build images, start services, and configure the database."
        echo ""
        
        # Setup state tracking
        SETUP_STATE_FILE="/tmp/monthli_setup_state"
        ROLLBACK_NEEDED=false
        
        # Error handling function
        handle_error() {
            echo "❌ Error in step: $1"
            echo "🔄 Attempting rollback..."
            ROLLBACK_NEEDED=true
            cleanup_on_error
            exit 1
        }
        
        # Cleanup function
        cleanup_on_error() {
            echo "🧹 Cleaning up failed setup..."
            cd docker 2>/dev/null && docker-compose down 2>/dev/null || true
            rm -f /tmp/monthli_setup_state 2>/dev/null || true
            echo "✅ Cleanup completed"
        }
        
        # Check if step already completed
        is_step_done() {
            grep -q "^$1$" "$SETUP_STATE_FILE" 2>/dev/null
        }
        
        # Mark step as done
        mark_step_done() {
            echo "$1" >> "$SETUP_STATE_FILE"
        }
        
        # Trap errors
        trap 'handle_error "Unknown step"' ERR
        
        # Step 1: Environment file
        if is_step_done "env_configured"; then
            echo "✅ Environment file already configured (skipping)"
        else
            echo "📝 Configuring environment file..."
            if cp .env.docker .env 2>/dev/null || cp .env.example .env; then
                mark_step_done "env_configured"
                echo "✅ Environment file configured"
            else
                handle_error "Environment file configuration"
            fi
        fi
        
        # Step 2: Build images
        if is_step_done "images_built"; then
            echo "✅ Docker images already built (skipping)"
        else
            echo "🔨 Building Docker images..."
            cd docker
            if docker-compose build --no-cache; then
                mark_step_done "images_built"
                echo "✅ Images built successfully"
            else
                handle_error "Docker image build"
            fi
        fi
        
        # Step 3: Start services
        if docker-compose ps | grep -q "Up"; then
            echo "✅ Services already running (skipping start)"
        else
            echo "🚀 Starting services..."
            if docker-compose up -d; then
                echo "✅ Services started"
            else
                handle_error "Service startup"
            fi
        fi
        
        # Step 4: Wait for database
        echo "⏳ Waiting for database to be ready..."
        for i in {1..30}; do
            if docker-compose exec -T postgres pg_isready -U monthli >/dev/null 2>&1; then
                echo "✅ Database is ready"
                break
            elif [ $i -eq 30 ]; then
                handle_error "Database readiness timeout"
            else
                echo "   Attempt $i/30..."
                sleep 2
            fi
        done
        
        # Step 5: Generate app key
        if is_step_done "app_key_generated"; then
            echo "✅ Application key already generated (skipping)"
        else
            echo "🔑 Generating application key..."
            if docker-compose exec -T app php artisan key:generate --force; then
                mark_step_done "app_key_generated"
                echo "✅ Application key generated"
            else
                handle_error "Application key generation"
            fi
        fi
        
        # Step 6: Run migrations
        if is_step_done "migrations_run"; then
            echo "✅ Migrations already run (skipping)"
        else
            echo "🗄️ Running database migrations..."
            if docker-compose exec -T app php artisan migrate --force; then
                mark_step_done "migrations_run"
                echo "✅ Database migrated"
            else
                handle_error "Database migration"
            fi
        fi
        
        # Step 7: Seed admin user
        if is_step_done "admin_seeded"; then
            echo "✅ Admin user already exists (skipping)"
        else
            echo "🌱 Creating admin user..."
            if docker-compose exec -T app php artisan db:seed --class=AdminUserSeeder --force; then
                mark_step_done "admin_seeded"
                echo "✅ Admin user created"
            else
                handle_error "Admin user seeding"
            fi
        fi
        
        # Step 8: Install dependencies
        if is_step_done "deps_installed"; then
            echo "✅ Dependencies already installed (skipping)"
        else
            # Install dev dependencies
            echo "📦 Installing composer dependencies..."
            if docker-compose exec -T app composer install --no-interaction; then
                echo "✅ Composer dependencies installed"
            else
                handle_error "Composer dependency installation"
            fi
            
            # Install and build frontend assets
            echo "🎨 Installing npm dependencies and building assets..."
            if docker-compose exec -T app npm ci && docker-compose exec -T app npm run build; then
                echo "✅ Frontend assets built"
            else
                handle_error "Frontend asset installation"
            fi
            
            # Publish Filament assets
            echo "🖼️ Publishing Filament assets..."
            if docker-compose exec -T app php artisan filament:assets; then
                mark_step_done "deps_installed"
                echo "✅ Filament assets published"
            else
                handle_error "Filament asset publishing"
            fi
        fi
        
        # Final health check
        echo "🏥 Running health check..."
        if curl -s http://localhost:8000/health >/dev/null 2>&1; then
            echo "✅ Application is responding"
        else
            echo "⚠️  Warning: Application may not be fully ready yet"
        fi
        
        echo ""
        echo "🎉 Setup completed successfully! Your development environment is ready."
        echo ""
        echo "📊 Access the admin panel:"
        echo "   URL: http://localhost:8000/admin"
        echo "   Email: admin@monthli.com"
        echo "   Password: admin123"
        echo ""
        echo "🛠️  Useful commands:"
        echo "   ./docker-dev.sh logs     - View logs"
        echo "   ./docker-dev.sh shell    - Open app shell"
        echo "   ./docker-dev.sh artisan  - Run artisan commands"
        echo "   ./docker-dev.sh down     - Stop services"
        echo "   ./docker-dev.sh reset    - Reset setup state"
        
        # Clear trap
        trap - ERR
        ;;
    "prod")
        echo "🚀 Starting production environment..."
        cp .env.docker .env 2>/dev/null || cp .env.example .env
        cd docker && docker-compose -f docker-compose.prod.yml up -d
        echo "✅ Production environment started!"
        ;;
    "prod-down")
        echo "🛑 Stopping production environment..."
        cd docker && docker-compose -f docker-compose.prod.yml down
        echo "✅ Production environment stopped!"
        ;;
    "reset")
        echo "🔄 Resetting setup state..."
        rm -f /tmp/monthli_setup_state
        echo "✅ Setup state cleared - you can run setup again from scratch"
        ;;
    "rollback")
        echo "🔙 Rolling back complete setup..."
        echo "⚠️  This will remove containers, volumes, and reset all data!"
        read -p "Are you sure? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            echo "🛑 Stopping containers..."
            cd docker 2>/dev/null && docker-compose down -v --remove-orphans 2>/dev/null || true
            
            echo "🗑️ Removing images..."
            docker rmi docker_app:latest docker_nginx:latest 2>/dev/null || true
            
            echo "💾 Removing volumes..."
            docker volume rm docker_postgres_data docker_redis_data 2>/dev/null || true
            
            echo "📄 Removing generated files..."
            rm -f .env /tmp/monthli_setup_state 2>/dev/null || true
            
            echo "🧹 Cleaning up Docker system..."
            docker system prune -f 2>/dev/null || true
            
            echo "✅ Complete rollback finished!"
            echo "💡 You can now run './docker-dev.sh setup' for a fresh start"
        else
            echo "❌ Rollback cancelled"
        fi
        ;;
    *)
        echo "🐳 Monthli Docker Development Helper"
        echo ""
        echo "Usage: $0 {setup|rollback|reset|up|down|logs|shell|artisan|migrate|seed|status|restart|prod|prod-down}"
        echo ""
        echo "Quick start:"
        echo "  setup       - ⚡ Complete dev environment setup (recommended for new devs)"
        echo "  rollback    - 🔙 Complete teardown (removes containers, volumes, data)"
        echo ""
        echo "Development commands:"
        echo "  up          - Start development environment"
        echo "  down        - Stop development environment"
        echo "  logs        - Show container logs"
        echo "  shell       - Open shell in app container"
        echo "  reset       - Reset setup state (for re-running setup)"
        echo "  artisan     - Run Laravel Artisan commands"
        echo "  migrate     - Run database migrations"
        echo "  seed        - Seed database with admin user"
        echo "  status      - Show container status"
        echo "  restart     - Restart all services"
        echo ""
        echo "Production commands:"
        echo "  prod        - Start production environment"
        echo "  prod-down   - Stop production environment"
        echo ""
        exit 1
        ;;
esac
