#!/usr/bin/env bash
set -euo pipefail

# scripts/worktree-setup.sh
# Maakt een Git worktree + geïsoleerde Docker stack voor parallelle ontwikkeling.
#
# Gebruik: ./scripts/worktree-setup.sh <feature-naam> [offset] [base-branch]
#   <feature-naam>: lowercase, bv. "degiro-export" of "auth-rewrite"
#   [offset]      : optioneel, integer (default: aantal bestaande worktrees)
#   [base-branch] : optioneel, basis-branch voor de worktree (default: main)
#
# Belangrijk (verschilt subtiel van de hoofd-dev flow):
# - De worktree draait NIET via ./docker-dev.sh up. Die helper kopieert
#   .env.docker over .env heen en zou de per-worktree APP_URL wegvagen.
#   Gebruik in een worktree altijd rechtstreeks:
#       cd docker && docker compose up -d
# - Docker Compose leest zijn eigen variabelen (COMPOSE_PROJECT_NAME, WEB_PORT)
#   uit docker/.env in de worktree. De root .env blijft de Laravel-config die in
#   de container gemount wordt (/var/www/.env). Twee losse bestanden, twee rollen.
# - Poorten starten op 18081 (basis 18080 + 1) om conflicten te vermijden met de
#   Monthli hoofd-dev (8000) en met AimTrack-worktrees (19080+). De poort wordt
#   automatisch gekozen als 'hoogste reeds toegewezen WEB_PORT + 1' (high-water-mark)
#   over de zuster-worktrees, zodat het verwijderen van een worktree nooit de poort
#   van een nog-actieve stack aan een nieuwe worktree teruggeeft.

NAME="${1:-}"
EXPLICIT_OFFSET="${2:-}"
BASE_BRANCH="${3:-main}"

if [[ -z "$NAME" ]]; then
  echo "Gebruik: $0 <feature-naam> [offset] [base-branch]" >&2
  exit 1
fi

if [[ ! "$NAME" =~ ^[a-z0-9][a-z0-9_-]*$ ]]; then
  echo "Feature-naam mag alleen lowercase letters, cijfers, '-' en '_' bevatten." >&2
  exit 1
fi

if [[ -n "$EXPLICIT_OFFSET" && ! "$EXPLICIT_OFFSET" =~ ^[0-9]+$ ]]; then
  echo "Offset (2e argument) moet een niet-negatief geheel getal zijn (of leeg voor automatisch)." >&2
  exit 1
fi

REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

WORKTREE_PATH="$REPO_ROOT/../monthli-${NAME}"
BRANCH="feature/${NAME}"
PROJECT_NAME="monthli_${NAME}"

if [[ -e "$WORKTREE_PATH" ]]; then
  echo "Pad bestaat al: $WORKTREE_PATH" >&2
  exit 1
fi

# De cleanup-instructies laten de branch bewust staan tot na merge. Vang daarom een
# her-run met dezelfde feature-naam af met een duidelijke melding i.p.v. een rauwe
# 'git worktree add -b'-fout.
if git show-ref --verify --quiet "refs/heads/$BRANCH"; then
  echo "Branch '$BRANCH' bestaat al (waarschijnlijk van een eerder verwijderde worktree)." >&2
  echo "Kies een andere feature-naam, of hang een worktree handmatig aan de bestaande branch:" >&2
  echo "  git worktree add \"$WORKTREE_PATH\" \"$BRANCH\"" >&2
  exit 1
fi

# Poort-toewijzing. Basis 18080; de eerste worktree krijgt 18081.
# Zonder expliciete offset: high-water-mark — de hoogste WEB_PORT die al aan een
# zuster-worktree (../monthli-*/docker/.env) is toegewezen, +1. Zo krijgt een nieuwe
# worktree nooit de poort van een nog-actieve stack terug, ook niet nadat een
# lager genummerde worktree is verwijderd.
BASE_PORT=18080
if [[ -n "$EXPLICIT_OFFSET" ]]; then
  # 10# forceert base-10, zodat "08"/"09" niet als (ongeldig) octaal worden gelezen.
  WEB_PORT=$((BASE_PORT + 10#$EXPLICIT_OFFSET))
else
  MAX_PORT="$BASE_PORT"
  for envfile in "$REPO_ROOT"/../monthli-*/docker/.env; do
    [[ -f "$envfile" ]] || continue
    existing="$(grep -E '^WEB_PORT=' "$envfile" 2>/dev/null | tail -1 | cut -d= -f2 | tr -d '[:space:]' || true)"
    if [[ "$existing" =~ ^[0-9]+$ ]] && (( existing > MAX_PORT )); then
      MAX_PORT="$existing"
    fi
  done
  WEB_PORT=$((MAX_PORT + 1))
fi

if ! git rev-parse --verify --quiet "$BASE_BRANCH" >/dev/null; then
  echo "Basis-branch '$BASE_BRANCH' bestaat niet lokaal. Run 'git fetch' of geef een geldige branch op als 3e argument." >&2
  exit 1
fi

echo "==> Worktree aanmaken: $WORKTREE_PATH (branch: $BRANCH, basis: $BASE_BRANCH)"
git worktree add -b "$BRANCH" "$WORKTREE_PATH" "$BASE_BRANCH"

# Kopieer .env.docker (fallback .env.example) zodat Laravel zelf z'n env heeft.
for candidate in .env.docker .env.example; do
  if [[ -f "$REPO_ROOT/$candidate" ]]; then
    cp "$REPO_ROOT/$candidate" "$WORKTREE_PATH/.env"
    echo "==> .env gekopieerd uit $candidate"
    break
  fi
done

# Stem APP_URL in de gekopieerde .env af op de worktree-poort. Zonder dit blijft
# APP_URL op de hoofd-dev poort (8000) staan, waardoor route()-gegenereerde URL's
# cross-origin naar de verkeerde stack wijzen (verkeerde CSRF/sessie-cookie, 419's).
if [[ -f "$WORKTREE_PATH/.env" ]]; then
  if grep -q '^APP_URL=' "$WORKTREE_PATH/.env"; then
    sed -i -E "s#^APP_URL=.*#APP_URL=http://localhost:${WEB_PORT}#" "$WORKTREE_PATH/.env"
  else
    echo "APP_URL=http://localhost:${WEB_PORT}" >> "$WORKTREE_PATH/.env"
  fi
  echo "==> APP_URL in .env afgestemd op poort ${WEB_PORT}"
fi

# Maak docker/.env in de worktree met ALLEEN docker-compose overrides.
# Deze .env wordt automatisch gelezen wanneer je 'cd docker && docker compose ...'
# draait (compose leest het .env-bestand in zijn working directory).
cat > "$WORKTREE_PATH/docker/.env" <<EOF
# Docker Compose variabele overrides (auto-gegenereerd door scripts/worktree-setup.sh)
# Gelezen door 'docker compose' vanuit de docker/ map van deze worktree.
COMPOSE_PROJECT_NAME=${PROJECT_NAME}
WEB_PORT=${WEB_PORT}
EOF

cat <<EOF

==> Worktree klaar: $WORKTREE_PATH

LET OP: gebruik in de worktree NIET './docker-dev.sh up' (die overschrijft .env).
Draai compose rechtstreeks vanuit de docker/ map.

Volgende stappen:
  cd $WORKTREE_PATH/docker
  docker compose up -d
  docker compose exec app php artisan key:generate --force
  docker compose exec app php artisan migrate --seed

Toegangs-URL's:
  Web:   http://localhost:${WEB_PORT}
  Admin: http://localhost:${WEB_PORT}/admin

Project name: ${PROJECT_NAME}  (containers/volumes/netwerk zijn hiermee geïsoleerd)

Vergeet niet de registry in .ai/guidelines/parallel-worktrees.md bij te werken.

Cleanup later:
  cd $WORKTREE_PATH/docker && docker compose down -v
  cd $REPO_ROOT && git worktree remove $WORKTREE_PATH
  git branch -d $BRANCH   # alleen na merge
EOF
