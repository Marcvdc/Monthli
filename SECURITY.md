# Security Policy

## Supported Versions
Only the `main` branch is actively supported.  
Older tags/releases may not receive security fixes.

## Reporting a Vulnerability
If you discover a security vulnerability:

1. **Do not** create a public GitHub issue.
2. Contact us privately via: marcvdcrommert@gmail.com
3. Provide as much detail as possible (steps, CSV samples if relevant, logs).
4. We will acknowledge receipt within 48 hours.
5. Fixes will be released as soon as possible. Coordinated disclosure is appreciated.

## Scope
- Application code in this repository.
- CSV import parsers (e.g. DEGIRO, transactions).
- Price ingest jobs and data normalization.

## Out of Scope
- External data sources (Yahoo Finance, CoinGecko, ECB).
- Issues caused by tampered or corrupted CSV files.
