#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
. "$ROOT_DIR/not_for_release/testFramework/load-test-environment.sh"
load_test_framework_env "$ROOT_DIR"

BASE_DATABASE="${ZC_TEST_DB_BASE_NAME:-db_testing}"
WORKER_COUNT="${ZC_TEST_DB_WORKERS:-${ZC_FEATURE_PARALLEL_PROCESSES:-4}}"
INCLUDE_BASE_DATABASE="${ZC_TEST_DB_INCLUDE_BASE:-1}"
DB_HOST="${ZC_TEST_DB_HOST:-${DB_SERVER:-127.0.0.1}}"
DB_PORT="${ZC_TEST_DB_PORT:-${MYSQL_TCP_PORT:-3306}}"
DB_USER="${ZC_TEST_DB_USER:-${DB_SERVER_USERNAME:-root}}"
DB_PASSWORD="${ZC_TEST_DB_PASSWORD:-${DB_SERVER_PASSWORD:-root}}"
DRY_RUN=0

usage() {
    cat <<EOF
Usage: $(basename "$0") [--base NAME] [--workers COUNT] [--skip-base] [--dry-run]

Recreates the worker-scoped test databases expected by the parallel test groundwork.

Environment overrides:
  ZC_TEST_DB_BASE_NAME      Base database name (default: db_testing)
  ZC_TEST_DB_WORKERS        Number of worker databases to create (default: ZC_FEATURE_PARALLEL_PROCESSES or 4)
  ZC_FEATURE_PARALLEL_PROCESSES
                            Fallback worker count for DB preparation when ZC_TEST_DB_WORKERS is unset
  ZC_TEST_DB_INCLUDE_BASE   Create the unsuffixed base database too (default: 1)
  ZC_TEST_DB_HOST           MySQL host (default: 127.0.0.1 or DB_SERVER)
  ZC_TEST_DB_PORT           MySQL port (default: 3306)
  ZC_TEST_DB_USER           MySQL user (default: root or DB_SERVER_USERNAME)
  ZC_TEST_DB_PASSWORD       MySQL password (default: root or DB_SERVER_PASSWORD)
  ZC_TEST_ENV_FILE          Optional env file loaded before defaults are resolved
  --dry-run                 Print the planned databases without creating them
EOF
}

validate_database_name() {
    local database_name="$1"

    if ! [[ "$database_name" =~ ^[A-Za-z0-9_]+$ ]]; then
        echo "Database name must contain only letters, numbers, and underscores: $database_name" >&2
        exit 2
    fi
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --base)
            BASE_DATABASE="${2:-}"
            shift
            ;;
        --workers)
            WORKER_COUNT="${2:-}"
            shift
            ;;
        --skip-base)
            INCLUDE_BASE_DATABASE=0
            ;;
        --dry-run)
            DRY_RUN=1
            ;;
        --help|-h)
            usage
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            usage >&2
            exit 2
            ;;
    esac
    shift
done

if ! [[ "$WORKER_COUNT" =~ ^[0-9]+$ ]] || [ "$WORKER_COUNT" -lt 1 ]; then
    echo "Worker count must be a positive integer." >&2
    exit 2
fi

if [ -z "$BASE_DATABASE" ]; then
    echo "Base database name must not be empty." >&2
    exit 2
fi

validate_database_name "$BASE_DATABASE"

if [ "$DRY_RUN" != "1" ] && ! command -v mysql >/dev/null 2>&1; then
    echo "mysql client not found in PATH." >&2
    exit 127
fi

declare -a DATABASES=()

if [ "$INCLUDE_BASE_DATABASE" = "1" ]; then
    DATABASES+=("$BASE_DATABASE")
fi

for worker in $(seq 1 "$WORKER_COUNT"); do
    DATABASES+=("${BASE_DATABASE}_${worker}")
done

for database in "${DATABASES[@]}"; do
    validate_database_name "$database"
done

if [ "$DRY_RUN" = "1" ]; then
    echo "Dry run for ${#DATABASES[@]} planned test database(s) on ${DB_HOST}:${DB_PORT} for user ${DB_USER}."
else
    echo "Recreating ${#DATABASES[@]} test database(s) on ${DB_HOST}:${DB_PORT} for user ${DB_USER}."
fi

mysql_args=(
    --host="$DB_HOST"
    --port="$DB_PORT"
    --user="$DB_USER"
    --password="$DB_PASSWORD"
    --batch
    --skip-column-names
)

for database in "${DATABASES[@]}"; do
    echo "RESET $database"
    if [ "$DRY_RUN" != "1" ]; then
        mysql "${mysql_args[@]}" -e "DROP DATABASE IF EXISTS \`$database\`; CREATE DATABASE \`$database\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
    fi
done

if [ "$DRY_RUN" = "1" ]; then
    echo "Planned databases:"
else
    echo "Recreated databases:"
fi
printf '  %s\n' "${DATABASES[@]}"
