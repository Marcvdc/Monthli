# PLAN – Portfolio Tracker & DEGIRO Import

Status: APPROVED  
Datum: 2025-11-21

## 1. Context
Monthli is een privacy-first portfolio tracker. Gebruiker importeert CSV’s van DEGIRO (startbalans + transacties), waarna de applicatie dagelijkse prijzen ophaalt en maandelijkse prestaties toont.

Wens: **Posities, cash, P&L en rendement moeten volledig afleidbaar zijn uit (1) één startbalans en (2) de volledige transactiestroom.** De `positions`-tabel is dan een afgeleide cache, geen bron van waarheid.

## 2. Doelstelling
- Eén eenduidige dataflow:
  - Startbalans op `balance_date` (portfolio CSV).
  - Alle gebeurtenissen daarna via transactions CSV’s.
- Volledige portfolio-staat (posities + cash) kan op elk moment worden herleid uit:
  - startbalans + transacties + prijs-/FX-data.
- Degiro-import UX is duidelijk en robuust (geen dubbele transacties, geen dubbel tellen).

## 3. Scope
### In scope
- Startbalans-import via DEGIRO portfolio CSV → `positions` + `balance_date`.
- Transactie-import via DEGIRO transactions CSV → `transactions` + updates naar `positions`/cash.
- Rebuild-mechanisme om posities/cash **volledig** opnieuw op te bouwen uit startbalans + transacties.
- Dagelijkse prijs- en FX-ingest + maandelijkse snapshots (reeds deels aanwezig, maar afstemmen op nieuwe waarheid).
- Basis-overlap/exposure inzicht per instrument over meerdere portfolios (minstens aggregation per ISIN/symbol).

### Out of scope (nu)
- Ondersteuning voor andere brokers dan DEGIRO.
- Complexe rapportage (sector/land/benchmark) – kan later bovenop posities/snapshots.

## 4. Huidige situatie (samenvatting)
- Modellen: Portfolio, Position, Transaction, Dividend, PriceTick, FxTick, MonthlySnapshot, Symbol.
- Import:
  - `StartingBalanceImportService` + `ImportStartingBalanceJob` → importeren portfolio CSV als startbalans in `positions` + `balance_date`.
  - `DegiroImportService` + parsers (`DegiroPositionsParser`, `DegiroTransactionsParser`, `DegiroDividendsParser`) + `ImportDegiroCsvJob` → verwerken DEGIRO CSV’s naar `transactions` (en indirect naar `positions`).
- Market data & snapshots:
  - `YahooClient` + jobs `IngestEquityPricesJob`/`IngestCryptoPricesJob`/`IngestFxRatesJob` + `MakeMonthlySnapshotJob`.
  - Console commands `IngestPrices`, `MakeSnapshot`, `SnapshotsBackfill`.
- UI:
  - Filament-resources voor Portfolio, Position, Transaction, MonthlySnapshot met import-acties.
- Scheduler & Docker:
  - Scheduler definieert prijs-ingest en snapshot commands.
  - `scheduler` service draait `php artisan schedule:work`.

Gaps t.o.v. doel:
- Geen expliciete **replay-service** die posities/cash herbouwt uit startbalans + transacties.
- Idempotentie / duplicate-detectie voor transaction-import is niet formeel vastgelegd.
- Overlap/exposure-rapportage ontbreekt in UI.

## 5. Acceptatiecriteria (AC)

- **AC1 – Deterministisch replaybaar**  
  Gegeven:
  - één geldige startbalans (positions CSV + `balance_date` per portfolio), en
  - alle transacties CSV’s vanaf `balance_date` in de juiste volgorde,
  kan het systeem voor een portfolio:
  - alle `positions` en cash volledig **opnieuw opbouwen** uit deze data,  
  - zodanig dat de gereplayde staat gelijk is aan de online bijgehouden staat (binnen afrondingsfouten).

- **AC2 – Geen dubbele transacties / double counting**  
  - Het opnieuw importeren van een identieke of overlappende DEGIRO transactions CSV leidt **niet** tot dubbele transactierecords.
  - Posities en cash veranderen niet na herimport van exact dezelfde bestanden.

- **AC3 – Strikte scheiding vóór/na balance_date**  
  - Voor `date < balance_date` is de portfolio-staat volledig bepaald door de startbalans.
  - Voor `date ≥ balance_date` is elke wijziging in posities/cash herleidbaar tot één of meer transactierecords.

- **AC4 – Tijdreizen per datum**  
  - Voor een willekeurige datum D kan het systeem:
    - de posities en cash-balance van een portfolio berekenen,
    - de totale waarde (in base currency) bepalen op basis van PriceTicks/FxTicks,  
    - en dit gebruiken in MonthlySnapshots (voor de eerste dag van de maand of een vast snapshot-moment).

- **AC5 – Overlap/exposure inzicht**  
  - Voor elk instrument (ISIN/symbol) kan de app tonen:
    - totale exposure over alle portfolios (som quantity * laatste prijs),
    - per portfolio breakdown (minstens value per portfolio).

## 6. Voorgestelde technische stappen (hoog-over)

**T1 – Importlaag aanscherpen**
- DEGIRO import:
  - Formeel vastleggen van transaction types (BUY, SELL, DIVIDEND, FEE, TAX, CASH_IN, CASH_OUT, etc.).
  - Elk geïmporteerd record krijgt minstens:
    - `import_batch_id` (UUID per import-run),
    - `source_file_name` + `source_file_hash`.
- Doel: idempotente imports en traceerbaarheid per CSV-bestand.
  
  Concreet voor het DEGIRO-transactions CSV-formaat (zoals `tests/Fixtures/csv/degiro_sample.csv`):
  - **BUY**
    - `Product != 'EUR'`
    - `Aantal > 0`
    - `Totaal < 0` (cash out in EUR)
  - **SELL**
    - `Product != 'EUR'`
    - `Aantal < 0`
    - `Totaal > 0` (cash in in EUR)
  - **DIVIDEND**
    - `Product != 'EUR'`
    - `Aantal = 0`
    - `Totaal > 0`
    - vaak Order ID prefix als `DIV...`
  - **CASH_IN** (storting)
    - `Product = 'EUR'`
    - `Totaal > 0`
  - **CASH_OUT** (opname)
    - `Product = 'EUR'`
    - `Totaal < 0`
  - **FEES / TAX**
    - Komen uit de kolom `Transactiekosten en/of` (en evt. belastingkolommen),
    - Worden als velden (`fees`, `tax`) op de BUY/SELL-transactie opgeslagen, niet als aparte transaction-rijen.

**T2 – Replay-service voor portfolio’s**
- Nieuwe service, bijv. `PortfolioReplayService` (naam nog te bepalen), die:
  - voor een gekozen portfolio alle `positions` en relevante cash-state reset/verwijdert (of in shadow-tabel schrijft),
  - startbalans (per `balance_date`) opnieuw toepast,
  - vervolgens alle transacties na `balance_date` in chronologische volgorde afspeelt,
  - en zo posities/cash herberekent.
- Aanroepbaar via:
  - Artisan command (debug / herstel),
  - optioneel Filament-actie “Rebuild from transactions”.

**T3 – Online updates alleen via transacties**
- Waar nu posities direct worden gemuteerd, zorgen dat dit:
  - gecentraliseerd gebeurt in een service die dezelfde logica gebruikt als de replay-service,
  - niet via ad-hoc edit in Filament (behalve expliciete startbalans mutaties indien nodig).

**T4 – Integratie met snapshots & prijzen**
- Zorgen dat `MakeMonthlySnapshotJob` uitgaat van dezelfde definitie van posities/cash als de replay-service.
- Controleren dat PriceTicks/FxTicks voldoende dekkend zijn om waarde op snapshot-momenten te berekenen.

**T5 – Overlap/exposure query + eenvoudige UI**
- Query’s/design voor exposure per instrument:
  - aggregatie over alle portfolios per ISIN/symbol,
  - eenvoudige Filament-pagina of widget voor exposure-overzicht.

## 7. Besluiten uit review
- B1: Eén portfolio representeert exact één DEGIRO-account.
- B2: We ondersteunen volledige historische data: alle transacties vanaf de eerste trade worden geïmporteerd (FULL_HISTORY-mode).
- B3: In deze FULL_HISTORY-architectuur wordt `balance_date` niet handmatig gekozen maar afgeleid als de dag vóór de eerste transactie. Een eventuele portfolio CSV kan worden gebruikt als sanity check / hulpmiddel, maar is niet nodig voor de kernberekeningen.

## 8. Open vragen
- (geen op dit moment)

---
**Status:** APPROVED – Dit PLAN is akkoord.  
Volgende stap: uitvoering van T1–T5 in kleine, getestte iteraties.
