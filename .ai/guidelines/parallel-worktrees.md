# Parallelle ontwikkeling via Git Worktrees

## Wanneer

Gebruik een aparte worktree zodra je een feature wilt ontwikkelen terwijl een andere stack (de hoofd-dev of een andere worktree) actief blijft. Denk aan:

- Parallelle tickets / feature branches
- Migratie-trajecten die de hoofd-dev niet mogen blokkeren
- Een nieuwe feature naast een werkende baseline testen

Niet gebruiken voor: hotfix op de huidige branch, kleine refactor in de huidige stack.

## Branch-model

Monthli werkt vandaag met één trunk: **`main`**. Worktrees worden daarom standaard afgesplitst van `main` (`feature/<naam>` → PR naar `main`). Wil je later een `develop`-integratiebranch invoeren, geef die dan mee als 3e argument aan het script:

```bash
./scripts/worktree-setup.sh <feature-naam> "" develop
```

## Hoofdrepo-discipline

De hoofd-clone (`.../Monthli`, branch `main`) is bedoeld als sync-knooppunt. Houd `git status` schoon:

- Untracked files in de hoofdrepo blokkeren `git pull --ff-only` zodra origin een file met dezelfde naam introduceert ("would be overwritten by merge"). Zo verandert een gemergde feature-PR (die een bijbehorend plan tracked maakt) een schijnbaar onschuldige lokale file in een blocker.
- De hoofdrepo werkt als basis voor elke nieuwe worktree: vies werk kruipt door op elke worktree die je vanaf `main` afsplitst.

## Vóór je `worktree-setup.sh` aanroept

1. **Lokale basis-branch bijwerken:** `git -C <hoofdrepo> pull --ff-only`. Het script splitst de nieuwe branch af van de **lokale** basis-branch; staat die achter, dan mist je worktree net-gemergde features (incl. migraties waar je plan op leunt).
2. **Hoofdrepo schoon:** `git status` in de hoofdrepo moet leeg zijn.
3. **Bevestig de afhankelijkheden:** leunt je feature op een nog niet-gemergde branch, splits dan vanaf die branch (`./scripts/worktree-setup.sh <naam> "" <basis-branch>`).

## Plan-files horen in de worktree

Schrijf plan-/scratch-bestanden **in de worktree**, niet in de hoofdrepo. Het plan reist dan mee op de feature-branch, wordt onderdeel van de PR-diff en blokkeert geen pulls op `main`.

## Hoe — altijd via het setup script

```bash
./scripts/worktree-setup.sh <feature-naam> [offset] [base-branch]
```

Het script:
1. Maakt `../monthli-<feature>` aan met branch `feature/<feature>` (default basis `main`)
2. Kopieert `.env.docker` (fallback `.env.example`) naar de worktree als **root `.env`** (Laravel-config) en stemt `APP_URL` af op de worktree-poort
3. Maakt een **aparte `docker/.env`** in de worktree met de compose-overrides (`COMPOSE_PROJECT_NAME`, `WEB_PORT`)
4. Print de URL's en cleanup-instructies

Vervolgens (let op: **vanuit de `docker/` map van de worktree**):

```bash
cd ../monthli-<feature>/docker
docker compose up -d
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --seed
```

> De worktree-flow gebruikt de **Docker Compose v2-plugin** (`docker compose`, met spatie). De oudere hoofd-dev helper `docker-dev.sh` gebruikt nog het v1-binary (`docker-compose`); beide lezen `.env` identiek uit de working directory, maar zorg dat de v2-plugin geïnstalleerd is.

## Twee `.env`-bestanden, twee rollen

| Bestand | Wie leest het | Inhoud |
|---|---|---|
| `<worktree>/.env` | Laravel (gemount als `/var/www/.env`) | volledige app-config, `APP_URL` op de worktree-poort |
| `<worktree>/docker/.env` | `docker compose` (working dir = `docker/`) | alleen `COMPOSE_PROJECT_NAME` + `WEB_PORT` |

Compose leest **niet** de root `.env`; Laravel leest **niet** `docker/.env`. Ze staan los van elkaar. Beide zijn via `.gitignore` (regel `.env`) uitgesloten van commits.

## Poort- en isolatiemodel

- Alleen de **nginx-poort** wordt naar de host gepubliceerd (`WEB_PORT`, default 8000, worktrees 18081+).
- De poort wordt automatisch toegewezen als **hoogste reeds gebruikte `WEB_PORT` + 1** (high-water-mark over `../monthli-*/docker/.env`). Daardoor is het veilig om een worktree te verwijderen: een nieuwe worktree pakt nooit de poort van een nog-actieve stack terug. Wil je toch een vaste poort forceren, geef dan een expliciete `offset` mee (2e argument) → poort = `18080 + offset`.
- Postgres en Redis worden **niet** naar de host gepubliceerd. Ze zijn per worktree geïsoleerd doordat elke stack een eigen `COMPOSE_PROJECT_NAME` heeft → eigen containers, volumes en netwerk. De app bereikt ze intern via `postgres:5432` / `redis:6379`.
- Wil je een DB-client rechtstreeks op één worktree-database aansluiten, dan is een per-worktree DB-forward-poort nodig — dat is bewust nog niet ingebouwd (zie "Vervolgpad / opties" onderaan).

## Verboden

- **Niet** `./docker-dev.sh up` (of `setup`) in een worktree draaien — die helper kopieert `.env.docker` over `.env` heen en vaagt de per-worktree `APP_URL` weg. Draai in een worktree altijd rechtstreeks `docker compose` vanuit `docker/`.
- **Niet** handmatig worktrees aanmaken zonder het script — dan lopen de poort-administratie en `.env`-isolatie uit de pas.
- **Niet** dezelfde branch in twee worktrees checkouten (Git verbiedt dit, maar zelf opletten).
- **Niet** plan-files of feature-werk in de hoofdrepo plaatsen — zie "Hoofdrepo-discipline".
- **Niet** `worktree-setup.sh` aanroepen met een achterlopende lokale `main` — pull eerst.

## Cleanup

```bash
cd ../monthli-<feature>/docker
docker compose down -v
cd -
git worktree remove ../monthli-<feature>
git branch -d feature/<feature>   # alleen na merge
```

Vergeet de registry hieronder niet bij te werken.

## Registry van actieve worktrees

| Feature | Branch | Pad | Web | Project name | Status |
|---|---|---|---|---|---|
| _hoofd-dev_ | `main` | `Monthli/` | 8000 | `docker` | actief |

Update deze tabel bij setup en cleanup — zo weet iedereen direct welke poort bij welke stack hoort.

## Cross-machine borging

Dit guideline-bestand staat in git, dus elke developer en elke Claude-sessie volgt dezelfde aanpak. MEMORY.md is **niet** geschikt — die is per-machine en reist niet mee.

## Vervolgpad / opties (nog te beslissen)

- **`develop`-integratiebranch** invoeren en het script daarop laten defaulten.
- **Per-worktree DB-forward-poort** toevoegen (postgres naar host publiceren) zodat een DB-GUI per worktree kan verbinden — vergt een compose-override zodat de hoofd-dev geen 5432 op de host claimt.
- **`docker-dev.sh` worktree-bewust** maken (bijv. `.env` niet overschrijven als die al bestaat) zodat de helper ook binnen een worktree veilig is.
- **AimTrack backporten**: AimTrack's `worktree-setup.sh` gebruikt nog de oude teloffset (`git worktree list | wc -l`) die na het verwijderen van een middelste worktree een poort kan hergebruiken. De high-water-mark-fix uit dit script is daar de moeite waard.
