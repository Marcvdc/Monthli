# Monthli — Privacy-First Portfolio Tracker (Laravel + Filament)

Privacy-first portfolio tracker. Import your broker CSV (e.g. DEGIRO), fetch daily prices automatically, and get clear monthly performance reports (MoM %, inflows/outflows, dividends). Built with Laravel + Filament.

Import your broker CSV (e.g., DEGIRO), fetch daily prices (Yahoo/CoinGecko/ECB), and get monthly performance reports (MoM %, inflows/outflows, dividends).
Stack: Laravel 11, Filament v3/v4, PostgreSQL, Redis/Horizon, Docker.

Features
- CSV import (DEGIRO NL/EN) — positions, transactions, dividends (idempotent).
- Daily price ingest — equities/ETF (Yahoo), crypto (CoinGecko), FX (ECB).
- Monthly snapshots — EUR-normalized value, MoM %, YTD, drawdown, volatility.
- Filament UI — tables, charts, bulk actions (Backfill, Force Snapshot).
- Export — CSV + PDF month report (AI TL;DR placeholder).
- Privacy-first — geen broker-credentials, alleen CSV.

Quickstart
1) Docker
 ````bash
   cp .env.example .env
   docker compose up -d --build
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --database=pgsql_direct
   docker compose exec app php artisan db:seed --database=pgsql_direct
   docker compose exec app php artisan horizon
````

   - App: http://localhost:8000
   - Horizon: http://localhost:8000/horizon
   - Filament: http://localhost:8000/admin
  
3) Lokale setup
 ````bask
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan serve
  ````
CSV Import (DEGIRO)
- positions.csv — actuele positie
- transactions.csv — buy/sell/fees
- dividends.csv — dividend en withholding tax

Minimal columns
- positions.csv: isin,symbol,name,quantity,avg_price,currency
- transactions.csv: date,isin,symbol,side(buy|sell),quantity,price,fee,total,currency
- dividends.csv: date,isin,symbol,amount,withholding_tax,currency

Snapshots & Metrics
- Dagelijkse prices @ 06:00 CET
- Snapshots @ 07:00 CET (per snapshot_day)
- KPIs: MoM %, YTD, drawdown, volatility

Backfill
````php
php artisan snapshots:backfill 2023-01 2025-08 --portfolio=ALL
````

UI (Filament)
- Resources: Portfolios, Positions, Snapshots
- Widgets: Linechart (waarde per maand), Barchart (MoM)
- Actions: Force Snapshot, Backfill

## Tests
````php
php artisan test
````

## Security & Observability

Configuration under `config/security.php` demonstrates encrypted API keys, security headers, basic rate limiting, and logging without PII.
- Geen broker-logins, CSV-only
- API keys encrypted (Eloquent casts)
- Geen PII in logs

License:  Apache License 2.0 — zie LICENSE

Roadmap
- CSV: BUX / Scalable / IBKR
- Symbol resolver UI
- AI TL;DR in PDF rapport
- Alerts (dividend, movements)
- Multi-portfolio & family mode
- SaaS mode + billing
