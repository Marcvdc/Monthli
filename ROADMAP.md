# 🚀 Roadmap – Monthli

> **Vervallen (juli 2026).** Deze roadmap wordt niet meer uitgevoerd; het project is bevroren.
> De vinkjes hieronder zijn bovendien te optimistisch — zie de statusnotitie in
> [README.md](README.md) en de motivatie in [MARKTONDERZOEK.md](MARKTONDERZOEK.md).
> Bewaard als historisch document.

## Docker Setup & Infrastructure 🟢 **RECENTLY COMPLETED**
- [x] Complete Docker environment (PHP 8.4, PostgreSQL 16, Redis, Nginx)
- [x] Docker development scripts (`docker-dev.sh`)
- [x] Automated Laravel permissions handling
- [x] GitHub Actions CI/CD with PHP 8.4
- [ ] Filament admin panel configuration (discovered missing)

## Fase 1 – Foundation 🟡 **PARTIALLY COMPLETED**
- [x] Docker-compose met PostgreSQL werkend
- [x] Laravel skeleton (basic setup)
- [ ] Filament admin panel properly configured
- [ ] Basis auth & users implementation
- [x] Repo governance

## Fase 2 – Data Ingest (CSV → DB) ✅ **COMPLETED**
- [x] CSV import DEGIRO
- [x] Transactions tabel + validatie
- [x] UI in Filament voor transacties

## Fase 3 – Market Data & Jobs ✅ **COMPLETED**
- [x] Daily job: actuele koers ophalen
- [x] Monthly snapshot in DB
- [x] Basis rapportage (waarde + delta)

## Fase 4 – Reporting & Insights 🟡 **IN PROGRESS**
- [x] Monthly Reports UI (grafieken)
- [x] Export (PDF/CSV)
- [x] Filters per asset/sector

## Fase 5 – Community & Extra's 🟡 **PARTIALLY DONE**
- [x] Multi-broker CSV support (DEGIRO implemented)
- [ ] API endpoints
- [x] Extra metrics (IRR, dividend yield)

## Fase 6 – Launch & Ops 🟡 **IN PROGRESS**
- [x] CI/CD pipeline
- [ ] Beta testers uitnodigen
- [ ] OSS positioning bepalen
