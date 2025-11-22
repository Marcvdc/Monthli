# Architecture – Monthli

Type: C: Hybrid (Eloquent + external price APIs)
Detected: 2025-11-21
Last Review: 2025-11-21

## Models Inventory
### Eloquent Models
- Portfolio (app/Models/Portfolio.php) - @eloquent
- Position (app/Models/Position.php) - @eloquent
- Transaction (app/Models/Transaction.php) - @eloquent
- MonthlySnapshot (app/Models/MonthlySnapshot.php) - @eloquent
- PriceTick (app/Models/PriceTick.php) - @eloquent

### API Models / Services
- YahooClient (app/Services/Prices/YahooClient.php) - @api

## Legacy Modules
- Admin Dashboard (Filament Resources under app/Filament/Resources/*) - legacy-ish UI, works, no refactor required right now.

## Refactor Candidates
- DEGIRO import pipeline (service + jobs) – consolidate parsing, detection, and idempotency story.
- Transaction-based portfolio updates – ensure positions & snapshots are driven fully by transactions.
