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
DB_PASSWORD="${ZC_TEST_DB_PASSWORD-${DB_SERVER_PASSWORD-root}}"
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
used_existing_databases=0

database_exists() {
    local database_name="$1"
    local output=""

    output="$(mysql "${mysql_args[@]}" -e "SELECT SCHEMA_NAME FROM information_schema.schemata WHERE SCHEMA_NAME = '${database_name}';" 2>/dev/null)" || return 2
    [ -n "$output" ]
}

handle_recreate_permission_error() {
    local error_file="$1"
    local -a missing_databases=()
    local -a unverifiable_databases=()

    if ! grep -Eq 'ERROR (1044|1045|1227)|Access denied' "$error_file"; then
        return 1
    fi

    for database_name in "${DATABASES[@]}"; do
        database_exists "$database_name"
        status=$?
        if [ "$status" -eq 0 ]; then
            continue
        fi

        if [ "$status" -eq 1 ]; then
            missing_databases+=("$database_name")
            continue
        fi

        unverifiable_databases+=("$database_name")
    done

    echo "MySQL user ${DB_USER} on ${DB_HOST}:${DB_PORT} does not have permission to recreate test databases." >&2

    if [ "${#unverifiable_databases[@]}" -gt 0 ]; then
        echo "Unable to verify whether these databases already exist: ${unverifiable_databases[*]}" >&2
        echo "Use a MySQL user that can CREATE/DROP databases, or pre-create the worker databases before running the feature suite." >&2
        return 1
    fi

    if [ "${#missing_databases[@]}" -gt 0 ]; then
        echo "Missing worker databases: ${missing_databases[*]}" >&2
        echo "Pre-create them with a privileged MySQL user, or rerun with a user that can CREATE/DROP databases." >&2
        return 1
    fi

    used_existing_databases=1
    echo "Existing worker databases detected; continuing without resetting them." >&2
    echo "If you need a clean run, pre-create/reset them with a privileged MySQL user or rerun with a user that can CREATE/DROP databases." >&2
    return 0
}

error_file=""
for database in "${DATABASES[@]}"; do
    echo "RESET $database"
    if [ "$DRY_RUN" != "1" ]; then
        error_file="$(mktemp "${TMPDIR:-/tmp}/zc-db-prepare.XXXXXX")"
        if ! mysql "${mysql_args[@]}" -e "DROP DATABASE IF EXISTS \`$database\`; CREATE DATABASE \`$database\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>"$error_file"; then
            if handle_recreate_permission_error "$error_file"; then
                rm -f "$error_file"
                error_file=""
                break
            fi
            cat "$error_file" >&2
            rm -f "$error_file"
            exit 1
        fi
        rm -f "$error_file"
        error_file=""
    fi
done

if [ "$DRY_RUN" = "1" ]; then
    echo "Planned databases:"
elif [ "$used_existing_databases" = "1" ]; then
    echo "Existing databases:"
else
    echo "Recreated databases:"
fi
printf '  %s\n' "${DATABASES[@]}"
