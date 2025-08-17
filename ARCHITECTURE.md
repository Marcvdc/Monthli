# Architecture

## Data model
- **Portfolios**: track base currency and snapshot day.
- **Positions**: symbol, quantity, average price, currency.
- **PriceTicks/FxTicks**: daily prices for equities, crypto, and FX.
- **MonthlySnapshots**: aggregated EUR value and metrics (MoM, YTD, drawdown, volatility).

## Jobs & Scheduler
- `IngestEquityPricesJob`, `IngestCryptoPricesJob`, `IngestFxRatesJob` run daily at 06:00 CET.
- `MakeMonthlySnapshotJob` calculates metrics per portfolio.
- Jobs are queued; Horizon supervises workers.

## Filament UI
- Resources: Portfolios, Positions, Snapshots.
- Widgets: Monthly value chart, monthly return chart.
- Actions: Import CSV, backfill snapshots, force snapshot.

## Exports
- `SnapshotExporter` outputs CSV for snapshots.
- `ReportGenerator` renders HTML/PDF monthly reports.

## Security
- No broker credentials stored; only CSV imports.
- API keys encrypted via Eloquent casts.
- Security headers and basic rate limiting configured in `config/security.php`.
