# Monthli

Privacy-first portfolio tracker. Import your broker CSV (e.g. DEGIRO), fetch daily prices automatically, and get clear monthly performance reports (MoM %, inflows/outflows, dividends). Built with Laravel + Filament.

## Development

This repository includes a basic Docker setup with services for the application, MySQL database, Redis cache, and a Horizon worker for queue processing.

Run the stack:

```bash
docker-compose up --build
```

A simple health endpoint is available at `/health.php` and returns `{"status":"ok"}` when the application container is running.

## Seed Data

A demo seeder (`DemoSeeder`) is included to populate a mock portfolio with price data for local experiments.

## Security & Observability

Configuration under `config/security.php` demonstrates encrypted API keys, security headers, basic rate limiting, and logging without PII.
