# System Inventory – Monthli

Datum: 2025-11-21

## Framework & Kernstack
- Laravel 12
- PHP 8.4
- Filament v4 (admin UI)
- Horizon (queue supervision, Redis backend)

## Domeinmodellen (app/Models)
- `Portfolio` – basisgegevens portfolio, base currency, snapshot settings, `balance_date`.
- `Position` – instrumentpositie (symbol, isin, name, quantity, average_price, currency).
- `Transaction` – ruwe brokertransacties (DEGIRO), uitgebreid via `update_transactions_table_for_degiro_import`.
- `Dividend` – dividend events.
- `PriceTick` – dagelijkse prijs per instrument.
- `FxTick` – FX rates.
- `MonthlySnapshot` – maandelijkse portfolio-waarden + performance metrics.
- `Symbol` – metadata per instrument (optioneel).
- `User` – authenticatie.

## Import & Services (app/Services)
- `DegiroImportService`
  - Coördineert DEGIRO CSV-import (met parsers onder `Services/Import`).
  - Schrijft naar `transactions` (+ indirect naar `positions`).
- `Services/Import`:
  - `DegiroDetector` – detecteert type DEGIRO CSV (portfolio/transactions/dividends).
  - `DegiroPositionsParser` – parse van portfolio CSV (huidige holdings).
  - `DegiroTransactionsParser` – parse van transactions CSV (buys, sells, cash, dividend, fees, tax).
  - `DegiroDividendsParser` – parse van dividend-specifieke exports.
- `StartingBalanceImportService`
  - Importeert DEGIRO portfolio CSV als **startbalans**.
  - Bouwt `positions` op vanuit kolommen `Product`, `Symbool/ISIN`, `Aantal`, `Slotkoers`, etc.
- `Services/Prices`:
  - `YahooClient` – externe prijsdata (equities) met throttling/retries.
  - Clients voor crypto/FX via jobs (`IngestCryptoPricesJob`, `IngestFxRatesJob`).

## Jobs (app/Jobs)
- `ImportDegiroCsvJob` – async verwerking van DEGIRO transactions CSV.
- `ImportStartingBalanceJob` – async verwerking van startbalans CSV naar `positions` + `balance_date`.
- `IngestEquityPricesJob`, `IngestCryptoPricesJob`, `IngestFxRatesJob` – dagelijkse prijs-/FX-ingest.
- `MakeMonthlySnapshotJob` – berekent maandelijkse snapshots (waarde, MoM, YTD e.d.).

## Console Commands (app/Console/Commands)
- Debug / test commands:
  - `DebugDegiroImport`, `TestDegiroImport`, `TestCsvImport`, `TestEnhancedImport` – handmatig testen van importflows.
  - `DebugPortfolioImport`, `DebugCsvParsing`, `TestSymbolExtraction` – inspectie van parsing, positions & balance_date.
- Market data & snapshots:
  - `IngestPrices` – handmatige prijsingest (per symbool of alle symbolen).
  - `MakeSnapshot` – handmatig snapshots genereren per portfolio of alle portfolios.
  - `SnapshotsBackfill` – historische snapshots backfillen.

## Filament Resources (app/Filament/Resources)
- `PortfolioResource`
  - CRUD voor portfolios.
  - Acties: DEGIRO portfolio CSV import (startbalans) en koppeling naar transaction-import.
  - View-pagina zonder inline importlogica (import via list-actie).
- `PositionResource`
  - Overzicht posities per portfolio.
  - Kolommen: Product (name), gecombineerd `Symbol/ISIN`-veld, quantity, average_price.
- `TransactionResource`
  - Overzicht transacties.
  - Header-actie voor import van DEGIRO transactions CSV (met portfolio-selectie).
- `MonthlySnapshotResource`
  - Overzicht maandelijkse snapshots per portfolio.

## Migrations (database/migrations)
- Basis tabellen (2025-08-17_075951..075959, 080001..080004):
  - `portfolios`, `positions`, `transactions`, `dividends`, `price_ticks`, `fx_ticks`, `monthly_snapshots`, `symbols`.
- Uitbreidingen:
  - `add_currency_to_positions_table` – currency voor positions.
  - `add_metrics_to_monthly_snapshots_table` – performance velden (MoM/YTD/etc.).
  - `add_snapshot_day_and_base_currency_to_portfolios_table` – instellingen per portfolio.
  - `update_transactions_table_for_degiro_import` – extra kolommen voor DEGIRO-transacties.
  - `add_isin_and_name_to_positions_table` – uitbreiding positions met ISIN + productnaam.
  - `add_balance_date_to_portfolios_table` – startbalans-datum per portfolio.
  - `make_positions_symbol_nullable` – symbol optioneel (ISIN-only instrumenten mogelijk).

## Scheduler & Docker
- `app/Console/Kernel.php`:
  - Scheduled commands (`prices:ingest`, `snapshot:make`, `snapshot:make --all`) op vaste tijden.
- `docker/docker-compose.yml`:
  - Services: `app`, `nginx`, `postgres`, `redis`, `horizon`, `scheduler`.
  - `scheduler` draait `php artisan schedule:work`.

## Observaties / Gaps (hoog-over)
- Startbalans wordt al geïmporteerd en `balance_date` vastgelegd.
- Transacties worden geïmporteerd en updaten posities, maar er is nog geen expliciete **replay-service** die:
  - vanuit startbalans + alle transacties posities en cash volledig opnieuw opbouwt.
- Idempotentie/duplicate-detectie bij transaction-import is nog niet formeel gedocumenteerd.
- Overlap/exposure-rapportage (posities over meerdere portfolios heen) is nog niet uitgewerkt in UI/queries.
