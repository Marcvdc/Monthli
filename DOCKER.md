# Docker setup

`docker-compose.yml` defines the development stack:

## Services
- **app** – Laravel application container.
- **postgres** – Postgres 16 database with persisted volume `pg_data`.
- **redis** – Redis cache/queue backend.
- **horizon** – queue worker + dashboard.

## Volumes
- `pg_data` stores Postgres data.
- `./storage` is mounted into the app container for logs/uploads.

## Health checks
Containers expose simple health checks:
```yaml
postgres:
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U \$${POSTGRES_USER}"]
    interval: 10s
    timeout: 5s
    retries: 5
```

## Common commands
```bash
# Start or rebuild containers
docker compose up -d --build
# Watch logs
docker compose logs -f app
# Run one-off commands
docker compose exec app php artisan migrate
# Stop stack
docker compose down
```

## Switch to Supabase
If you want hosted Postgres:
1. Disable the `postgres` service:
   ```bash
   docker compose rm -sf postgres
   ```
2. Update `.env` with Supabase credentials (`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`, `DB_PORT`).
3. Restart the remaining services: `docker compose up -d`.

