# Usage

## Enhanced DEGIRO Import Process

### 1. Setting Up Starting Balance
Before importing transactions, establish your portfolio's starting positions:

1. Export your portfolio from DEGIRO (Portfolio → Export → CSV)
2. In the Filament admin panel, go to **Portfolios**
3. Click **Import Starting Balance** on your portfolio
4. Upload the DEGIRO portfolio CSV and set the balance date
5. The system will clear existing positions and import your starting balance

DEGIRO Portfolio CSV format:
```text
Product,Symbool/ISIN,Aantal,Slotkoers,Lokale waarde,,Waarde in EUR
ASML Holding NV,NL0010273215,10,850.50,8505.00,,8505.00
Apple Inc,AAPL,25,175.25,4381.25,,4381.25
```

### 2. Importing Transaction History
After setting starting balance, import your DEGIRO transaction exports:

1. Export account overview from DEGIRO (Account → Export → CSV)
2. Go to **Transactions** in the admin panel
3. Click **Import DEGIRO CSV** and select your portfolio
4. Upload your transaction CSV file

DEGIRO Transaction CSV format (19 columns):
```text
Datum,Tijd,Product,ISIN,Beurs,Aantal,Koers,Totaal,Order ID,Valuta,FX,Valutakoers,Kosten,Totaal in EUR,...
```

### 3. Overlapping Uploads
The system prevents duplicate imports through enhanced detection:
- Transactions with Order IDs: matched by external_id
- Transactions without Order IDs: matched by date + symbol/ISIN + quantity + price
- Monthly CSV uploads are safe - duplicates will be automatically skipped

### 4. Balance Date Coordination
- Transactions before the portfolio's `balance_date` are imported but don't affect position calculations
- Only transactions after `balance_date` update position quantities and average prices
- This prevents double-counting when combining starting balances with transaction history

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
