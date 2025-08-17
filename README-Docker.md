# Docker Development Environment - Monthli

Deze Docker setup biedt een complete ontwikkelomgeving voor Monthli met alle benodigde services.

## Services

- **PHP 8.4-FPM** - Laravel applicatie met PostgreSQL support
- **Nginx** - Web server
- **PostgreSQL 16** - Database
- **Redis** - Cache & sessies
- **Node.js** - Asset compilation
- **Laravel Horizon** - Queue worker

## Quick Start

### 1. Eerste keer opstarten

```bash
# Start de omgeving
./docker-dev.sh up

# Installeer dependencies
./docker-dev.sh composer

# Genereer application key
./docker-dev.sh artisan key:generate

# Run migraties
./docker-dev.sh migrate

# (Optioneel) Seed de database
./docker-dev.sh seed
```

### 2. Toegang tot de applicatie

- **Web applicatie**: http://localhost:8000
- **Database**: localhost:5432 (user: monthli, password: secret, database: monthli)
- **Redis**: localhost:6379

## Beschikbare Commando's

```bash
./docker-dev.sh up        # Start alle services
./docker-dev.sh down      # Stop alle services
./docker-dev.sh build     # Rebuild Docker images
./docker-dev.sh logs      # Bekijk logs
./docker-dev.sh shell     # Open shell in app container
./docker-dev.sh status    # Bekijk container status
./docker-dev.sh restart   # Herstart services

# Laravel specifiek
./docker-dev.sh artisan [command]  # Run artisan commando's
./docker-dev.sh migrate            # Run migraties
./docker-dev.sh fresh              # Fresh migrate + seed
./docker-dev.sh test               # Run tests

# Package management
./docker-dev.sh composer   # Composer install
./docker-dev.sh npm [cmd]  # NPM commando's
```

## Development Workflow

### Code wijzigingen
Je code wordt automatisch gesynchroniseerd tussen je host en containers via volumes.

### Database wijzigingen
```bash
./docker-dev.sh artisan make:migration create_posts_table
./docker-dev.sh migrate
```

### Frontend assets
```bash
./docker-dev.sh npm install
./docker-dev.sh npm run dev
```

### Queue jobs
Laravel Horizon draait automatisch voor queue processing.

## Troubleshooting

### Container start niet
```bash
./docker-dev.sh logs
```

### Database connectie problemen
Controleer of de database service draait:
```bash
./docker-dev.sh status
```

### Permissie problemen
```bash
./docker-dev.sh shell
chown -R www-data:www-data storage bootstrap/cache
```

### Cache leegmaken
```bash
./docker-dev.sh artisan cache:clear
./docker-dev.sh artisan config:clear
./docker-dev.sh artisan view:clear
```

## Configuratie

### Environment variabelen
De `.env.docker` file wordt automatisch gekopieerd naar `.env` bij het opstarten.

### PHP configuratie
Pas `docker/php/local.ini` aan voor PHP instellingen.

### Nginx configuratie  
Pas `docker/nginx/conf.d/app.conf` aan voor web server instellingen.

### PostgreSQL configuratie
PostgreSQL gebruikt standaard configuratie. Voor custom instellingen kun je een postgresql.conf volume toevoegen.

## Tips

- Gebruik `./docker-dev.sh shell` om direct in de container te werken
- Logs zijn beschikbaar via `./docker-dev.sh logs`
- Database data wordt bewaard in een Docker volume
- Voor productie gebruik je een andere Docker configuratie

## Vereisten

- Docker
- Docker Compose
- Gebruiker toegevoegd aan docker groep: `sudo usermod -aG docker $USER`

Na het toevoegen aan de docker groep, log uit en weer in voor de wijzigingen.
