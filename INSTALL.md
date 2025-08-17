# Installatie

## Switch naar Supabase

Standaard draait Monthli lokaal met de meegeleverde PostgreSQL-container. Wil je in plaats daarvan een Supabase-instantie gebruiken, stel dan de `DB_DIRECT_*` variabelen in je `.env` bestand in met de Supabase "direct connection" gegevens:

```env
DB_DIRECT_HOST=your.supabase.host
DB_DIRECT_PORT=5432
DB_DIRECT_DATABASE=your_db
DB_DIRECT_USERNAME=your_user
DB_DIRECT_PASSWORD=your_password
DB_DIRECT_SCHEMA=public
DB_DIRECT_SSLMODE=require
```

Voer daarna de migraties en seeds uit via de `pgsql_direct` connectie:

```bash
php artisan migrate --database=pgsql_direct
php artisan db:seed --database=pgsql_direct
```
