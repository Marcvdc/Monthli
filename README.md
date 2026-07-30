# Monthli — Laravel/Filament referentieproject

> ### Status: bevroren (juli 2026)
>
> **Dit is geen SaaS en wordt niet actief onderhouden.** Monthli is een publiek
> referentieproject in Laravel 12 / Filament v4 / PostgreSQL 16 / Redis + Horizon / Docker,
> met tests en GitHub Actions CI. Je kunt het lokaal draaien en de code lezen; er is geen
> gehoste dienst, geen registratie en geen support.
>
> De commerciële ambitie is in juli 2026 gesloten na marktonderzoek. De onderbouwing —
> concurrentieanalyse, marktomvang, betalingsbereidheid en de rekensom — staat in
> [MARKTONDERZOEK.md](MARKTONDERZOEK.md). Korte versie: de differentiator (DEGIRO-CSV,
> privacy-first, geen broker-credentials) is inmiddels gratis verkrijgbaar bij TrackinV en
> voor €29 eenmalig sterker uitgevoerd bij FolioInsights.
>
> **Zoek je wél een werkende portfolio tracker?** Kijk naar
> [Portfolio Dividend Tracker](https://portfoliodividendtracker.com) (NL),
> [TrackinV](https://trackinv.com), [FolioInsights](https://folioinsights.app),
> [Capitalyse](https://capitalyse.app), of — self-hosted en open source —
> [Ghostfolio](https://github.com/ghostfolio/ghostfolio) en
> [Portfolio Performance](https://www.portfolio-performance.info).
>
> Pull requests worden niet meer beoordeeld. Fork vrij: Apache-2.0.

Privacy-first portfolio tracker. Import your broker CSV (e.g. DEGIRO), fetch daily prices automatically, and get clear monthly performance reports (MoM %, inflows/outflows, dividends). Built with Laravel + Filament.

Import your broker CSV (e.g., DEGIRO), fetch daily prices (Yahoo/CoinGecko/ECB), and get monthly performance reports (MoM %, inflows/outflows, dividends).
Stack: Laravel 12, Filament v4, PostgreSQL 16, Redis/Horizon, Docker, PHP 8.4.

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

## Development Status

> **Historisch — stand van augustus/november 2025, niet bijgewerkt.** De faseplanning hieronder
> is nooit afgemaakt en de statusvinkjes zijn optimistisch: de kern-importflow werkt niet
> end-to-end op `main` (de echte importcode zat in PR #53, die ongemerged is gesloten),
> `tests/Feature/ImportFlowTest.php` is een stub, en er is geen registratie, multi-tenancy of
> publieke webinterface — alle functionaliteit zit in het Filament-adminpanel. Fase 5 en 6
> komen er niet.

### Phase 1: Foundation ✅ COMPLETE
- ✅ Docker-compose + PostgreSQL setup
- ✅ Laravel + Filament skeleton with auth & users
- ✅ Admin panel with Portfolio, Position, MonthlySnapshot resources
- ✅ Custom dashboard widgets (MonthlyValueChart, MonthlyReturnChart)
- ✅ Laravel Horizon for advanced queue management
- ✅ Production-ready Docker environment with one-command setup

### Phase 2: Data Ingest 🚧 IN PROGRESS
**Completed:**
- ✅ Transaction model with DEGIRO-specific fields (symbol, ISIN, currency, fees, venue)
- ✅ DegiroImportService with Dutch→English transaction mapping
- ✅ TransactionResource for Filament with filtering and CRUD
- ✅ ImportDegiroCsvJob for queue-based CSV processing
- ✅ CSV upload UI in admin panel ("Import DEGIRO CSV" button)
- ✅ Filament v4 compatibility fixes

**Bekende problemen op het moment van bevriezen (niet opgelost):**
- 🔧 CSV-importflow: geïmporteerde data verschijnt niet — nooit uitgezocht
- 🔧 Docker-permissieproblemen bij log-/tinker-commando's
- 🔧 Nooit end-to-end getest met echte DEGIRO-CSV-bestanden

**Niet gebouwd:**
- Add CSV file upload validation
- Implement transaction data validation rules
- Add transaction validation error reporting
- Create transaction duplicate detection
- Add transaction category/type classification

### Phase 3: Market Data & Jobs (PLANNED)
- Daily price jobs for equities/crypto/FX
- Monthly snapshots automation
- Basic reporting functionality

### Phase 4: Reporting & Insights (PLANNED)
- Monthly reports UI with charts
- PDF/CSV export capabilities
- Advanced filtering and metrics

### Phase 5: Community & Extras (PLANNED)
- Multi-broker support (BUX, Scalable, IBKR)
- API endpoints
- Additional metrics (IRR, dividend yield)

### Phase 6: Launch & Ops (PLANNED)
- CI/CD pipeline
- Beta testing
- Open source positioning

## Quick Setup

```bash
# One-command development setup
./docker-dev.sh setup

# Access points
# - App: http://localhost:8000
# - Admin: http://localhost:8000/admin (admin@monthli.com / admin123)
# - Horizon: http://localhost:8000/horizon

# Rollback everything
./docker-dev.sh rollback
```

License:  Apache License 2.0 — zie LICENSE
