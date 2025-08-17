# Usage

## Importing DEGIRO CSV
1. In the Filament admin panel, go to **Positions**.
2. Click **Import** and upload your `positions.csv` exported from DEGIRO.
3. Repeat for transactions and dividends if available.

CSV format (minimal columns):
```text
positions.csv: isin,symbol,name,quantity,avg_price,currency
transactions.csv: date,isin,symbol,side,quantity,price,fee,total,currency
dividends.csv: date,isin,symbol,amount,withholding_tax,currency
```

## Snapshots
Take a monthly valuation snapshot for a portfolio:
```bash
docker compose exec app php artisan snapshots:backfill 2024-01 2024-12 --portfolio=ALL
```
The scheduler runs daily price ingests at 06:00 CET and snapshots at 07:00 CET.

## Exports
Generate a CSV of snapshot metrics:
```bash
docker compose exec app php artisan snapshots:export --portfolio=1 > snapshots.csv
```
Create a PDF report:
```bash
docker compose exec app php artisan snapshots:report --portfolio=1 > report.pdf
```

## Filament UI
- **Portfolios**: manage base currency & snapshot day.
- **Positions**: view holdings, bulk import, or force snapshot.
- **Snapshots**: browse monthly metrics and export.

Access the UI at `http://localhost:8000/admin`.

## Daily jobs
To run scheduled jobs manually:
```bash
docker compose exec app php artisan schedule:run
```
Use Horizon to monitor queues at `http://localhost:8000/horizon`.
