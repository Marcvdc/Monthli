# PLAN – Laravel & Filament upgrade voor Monthli

Status: APPROVED  
Datum: 2025-11-22

## 1. Context
- Project gebruikt al recente stack:
  - `laravel/framework`: ^12.0
  - `filament/filament`: ^4.0
- Wens: het project structureel op de **laatste stabiele minor/patches** houden, met gecontroleerde upgrades:
  - Duidelijk **upgrade-plan** per framework-bump.
  - Gescheiden branches voor feature‑werk (DEGIRO/portfolio) vs. framework-upgrades.

## 2. Doelstelling
- Een herhaalbaar proces voor Laravel + Filament upgrades, met:
  - Minimale downtime / broken UI.
  - Volledige regressietest op kernflows (imports, replay, snapshots).
  - Duidelijke rollback-strategie (branching).

## 3. Scope
### In scope
- Upgrade PLAN en branch-strategie.
- Bijwerken van `composer.json` naar laatste compatibele versies binnen major:
  - Laravel 12.x → laatste 12.x.
  - Filament 4.x → laatste 4.x.
- Uitvoeren van `composer update` voor relevante packages.
- Aanpassen van code waar nodig (bijv. deprecated APIs, Filament breaking changes binnen 4.x).
- Basis smoke/regressietests op:
  - Portfolio aanmaken.
  - Startbalans import.
  - DEGIRO transacties import.
  - Replay-service (T2) en snapshots.

### Out of scope (nu)
- Upgrade naar toekomstige Laravel 13.x of Filament 5.x.
- Grote UI-herontwerpen.
- Migratie naar ander admin-framework.

## 4. Acceptatiecriteria (AC)
- **AC1 – Gescheiden branches en nette commits**  
  - Huidige DEGIRO/T2-wijzigingen staan op een feature-branch met minimaal één duidelijke commit (bijv. `feature/degiro-import-t2`).
  - Laravel/Filament upgrade gebeurt op afzonderlijke branch (bijv. `chore/upgrade-laravel-filament-12.x`), zonder functionele business-wijzigingen.

- **AC2 – App start probleemloos**  
  - `php artisan migrate` draait zonder fouten.  
  - `php artisan serve` (of Docker stack) start zonder fatals.

- **AC3 – Admin UI functioneel**  
  - Filament dashboard en resources (Portfolio, Position, Transaction, MonthlySnapshot) laden zonder errors.
  - Basis CRUD-acties werken (view, list, create).

- **AC4 – Kernflows blijven werken**  
  - Startbalans-import (DEGIRO portfolio CSV) werkt end-to-end.
  - Transactie-import (DEGIRO CSV) werkt end-to-end.
  - Replay-service voor portfolio (T2) werkt nog en levert consistente posities.
  - Maandelijkse snapshot-job (`MakeMonthlySnapshotJob`) draait succesvol op ten minste één portfolio.

- **AC5 – Tests & linting groen**  
  - Alle bestaande tests draaien groen.  
  - Linter (Pint) draait zonder blocking issues.

## 5. Technische aanpak (stappenplan)

### Stap U1 – Branching & commit van huidige werk
1. Maak een nieuwe feature-branch vanaf huidige hoofdbranch (bijv. `main` of `develop`):
   - Naam voorstel: `feature/degiro-import-t2`.
2. Commit alle huidige DEGIRO/T2-gerelateerde wijzigingen:
   - Services: `DegiroImportService`, `PortfolioReplayService`, etc.
   - Jobs/commands: `ImportDegiroCsvJob`, `ReplayPortfolio`, `MakeSnapshot`, etc.
   - Filament-resources: Transaction/Portfolio/Position UI-aanpassingen.
   - Migrations & docs (PLAN, system-inventory, architecture).
3. Push branch naar remote.

### Stap U2 – Nieuwe upgrade-branch
1. Maak vanuit **up-to-date hoofdbranch** (na merge of rebase van feature-werk) een chore-branch:
   - Voorstel: `chore/upgrade-laravel-filament-12.x`.
2. Geen functionele wijzigingen in deze branch behalve wat strikt nodig is voor de upgrade.

### Stap U3 – Composer-versies & update
1. Controleer huidige versies met `composer outdated laravel/framework filament/filament laravel/horizon`.
2. Update `composer.json` indien nodig binnen major:
   - `"laravel/framework": "^12.0"` → blijft meestal hetzelfde; updates via lockfile.
   - `"filament/filament": "^4.0"` → idem.
3. Draai daarna:
   - `composer update laravel/framework filament/filament laravel/horizon --with-all-dependencies`.
4. Noteer effectieve versies in dit PLAN of in `docs/Monthli/tech/system-inventory-YYYY-MM-DD.md`.

### Stap U4 – Fixes voor breaking/deprecated changes
1. Volg Laravel 12.x en Filament 4.x upgrade notes/release notes (extern).
2. Los compile/runtime errors op:
   - Namespaces/wijzigingen in Filament componenten.
   - Eventuele veranderingen in route-registratie, middleware, auth, etc.
3. Zorg dat alle Filament-resources en custom pages opnieuw laden.

### Stap U5 – Smoke/regressietests
1. Handmatige stappen (minimaal):
   - Login op admin.
   - Portfolio aanmaken.
   - Startbalans CSV importeren.
   - DEGIRO transacties CSV importeren.
   - Replay-service draaien voor dit portfolio.
   - Een snapshot genereren (handmatig via command of job).
2. Automatische checks:
   - `php artisan test`.
   - `./vendor/bin/pint` (of `composer pint`).

### Stap U6 – Review & merge
1. Laat upgrade-branch reviewen (code + PLAN).  
2. Na goedkeuring: merge naar hoofdbranch met duidelijke commit message (bijv. `chore: upgrade laravel 12.x & filament 4.x`).
3. Tag desnoods een release (bijv. `v0.2.0-upgrade-l12-f4`).

## 6. Rollback-strategie
- Bij problemen in de upgrade-branch:
  - Branch kan eenvoudig worden gedropt zonder impact op DEGIRO/T2-feature-branch.
- Indien al gemerged naar hoofdbranch:
  - Rollback via git revert op de upgrade-commit(s).
  - Composer lock terugzetten naar vorige versie (via eerdere commit).

## 7. Open punten
- Nog te beslissen:
  - Welke branch exact de "source of truth" is (`main` vs. `develop`).
  - Of we CI aanhaken om tests/lint bij elke upgrade-branch automatisch te draaien.

---
**Status:** APPROVED – PLAN is goedgekeurd; uitvoering (branching + upgrades) kan starten volgens stappen U1–U6.
