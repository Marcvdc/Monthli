# Installation

This guide sets up a local development environment for Monthli using Docker.

## Prerequisites
- [Docker](https://www.docker.com/) + Docker Compose
- Git
- Optional: [Supabase](https://supabase.com) account for hosted Postgres

### Clone the repo
```bash
git clone https://github.com/marcvdc/monthli.git
cd monthli
```

### Configure environment
Copy the example file and adjust database settings for the containers:
```bash
cp .env.example .env
```

Update `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=monthli
DB_USERNAME=monthli
DB_PASSWORD=secret

REDIS_HOST=redis
QUEUE_CONNECTION=redis
```

### Start services
```bash
docker compose up -d --build
```

Install PHP & JS dependencies and run migrations:
```bash
docker compose exec app composer install
# front‑end assets (optional)
docker compose exec app npm install

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Visit http://localhost:8000 for the app and http://localhost:8000/admin for the Filament panel.

### Optional: Supabase instead of local Postgres
1. Comment or remove the `postgres` service in `docker-compose.yml`.
2. In `.env`, point the DB variables to your Supabase project:
```env
DB_HOST=db.<project>.supabase.co
DB_PORT=6543
DB_USERNAME=postgres
DB_PASSWORD=<supabase-password>
```
3. Restart the containers: `docker compose up -d`.

### WSL hints
- Enable [WSL2](https://learn.microsoft.com/en-us/windows/wsl/install) and ensure Docker Desktop uses the WSL backend.
- Store the project under `/home/<user>/` for best file‑watching performance.
- Use `wsl.exe` to run Docker commands from Windows Terminal if needed.

