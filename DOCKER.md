# Docker Setup

The development stack uses PostgreSQL 16 alongside Redis and Horizon. Below is the service definition used in `docker-compose.yml`:

```yaml
postgres:
  image: postgres:16
  restart: unless-stopped
  environment:
    POSTGRES_DB: ${DB_DATABASE:-monthli}
    POSTGRES_USER: ${DB_USERNAME:-monthli}
    POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
  volumes:
    - postgres_data:/var/lib/postgresql/data
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME:-monthli} -d ${DB_DATABASE:-monthli}"]
    interval: 5s
    retries: 5

volumes:
  postgres_data:
```

The `app` service depends on this Postgres container and on Redis. Run `docker compose up -d --build` to start all services.
