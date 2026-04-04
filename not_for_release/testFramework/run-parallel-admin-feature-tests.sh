#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
. "$ROOT_DIR/not_for_release/testFramework/load-test-environment.sh"
load_test_framework_env "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-php}"
PHPUNIT_BIN="${PHPUNIT_BIN:-$ROOT_DIR/vendor/bin/phpunit}"
PROCESS_COUNT="${ZC_FEATURE_PARALLEL_PROCESSES:-${ZC_TEST_DB_WORKERS:-2}}"
PROGRESS_INTERVAL="${ZC_TEST_PROGRESS_INTERVAL:-15}"
DB_BASE_NAME="${ZC_TEST_DB_BASE_NAME:-db}"
DB_HOST="${ZC_TEST_DB_HOST:-${DB_SERVER:-127.0.0.1}}"
DB_PORT="${ZC_TEST_DB_PORT:-${MYSQL_TCP_PORT:-3306}}"
DB_USER="${ZC_TEST_DB_USER:-${DB_SERVER_USERNAME:-root}}"
DB_PASSWORD="${ZC_TEST_DB_PASSWORD:-${DB_SERVER_PASSWORD:-root}}"
WORK_DIR="$(mktemp -d "${TMPDIR:-/tmp}/zc-admin-feature-parallel.XXXXXX")"
TEST_LIST_FILE="$WORK_DIR/test-files.txt"
TEST_FILTER="${ZC_FEATURE_TEST_FILTER:-}"
declare -a EXTRA_PHPUNIT_ARGS=()
CLI_FILTER=""
DRY_RUN=0
PREPARE_DATABASES=0

declare -A PID_TO_FILE=()
declare -A PID_TO_SLUG=()
declare -A PID_TO_WORKER=()
declare -A PID_TO_STARTED_AT=()
declare -a AVAILABLE_WORKERS=()
ACQUIRED_WORKER=""
TOTAL_TESTS=0
TOTAL_ASSERTIONS=0

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [--prepare-databases] [phpunit-args...]

Runs admin feature test files tagged @group parallel-candidate in parallel,
assigning each running process a worker token via ZC_TEST_WORKER.

Useful environment variables:
  ZC_FEATURE_PARALLEL_PROCESSES   Number of worker processes (default: ZC_TEST_DB_WORKERS or 2)
  ZC_TEST_DB_WORKERS              Fallback worker count when ZC_FEATURE_PARALLEL_PROCESSES is unset
  ZC_TEST_DB_BASE_NAME            Base database name used for worker DB checks/preparation (default: db)
  ZC_FEATURE_TEST_FILTER          Substring filter applied to candidate file paths before launch
  ZC_TEST_PROGRESS_INTERVAL       Seconds between in-flight progress updates while waiting (default: 15)
  ZC_TEST_ENV_FILE                Optional env file loaded before DB defaults are resolved
  PHP_BIN                         PHP executable to use (default: php)
  PHPUNIT_BIN                     PHPUnit binary to use (default: vendor/bin/phpunit)

Examples:
  composer feature-tests-admin-parallel -- --dry-run
  composer feature-tests-admin-parallel
  composer feature-tests-admin-parallel -- --prepare-databases
  composer feature-tests-admin-parallel -- --filter AdminEndpointsTest
  composer feature-tests-admin-parallel-local
  ZC_FEATURE_TEST_FILTER=AdminEndpoints composer feature-tests-admin-parallel -- --dry-run
EOF
}

apply_filter() {
    local source_file="$1"
    local filter_value="$2"
    local filtered_file="$source_file.filtered"

    awk -v filter="$filter_value" '
        {
            file = $0
            name = file
            sub(/^.*\//, "", name)
            stem = name
            sub(/\.php$/, "", stem)

            if (name == filter || stem == filter) {
                exact[++exact_count] = file
            }
            if (index(file, filter) > 0) {
                partial[++partial_count] = file
            }
        }
        END {
            if (exact_count > 0) {
                for (i = 1; i <= exact_count; i++) {
                    print exact[i]
                }
            } else {
                for (i = 1; i <= partial_count; i++) {
                    print partial[i]
                }
            }
        }
    ' "$source_file" > "$filtered_file"

    mv "$filtered_file" "$source_file"
}

cleanup() {
    rm -rf "$WORK_DIR"
}

accumulate_phpunit_counts() {
    local output_file="$1"
    local summary_line=""

    summary_line="$(grep -E 'OK \([0-9]+ tests?, [0-9]+ assertions?\)|Tests: [0-9]+, Assertions: [0-9]+' "$output_file" | tail -n 1 || true)"

    if [[ "$summary_line" =~ OK\ \(([0-9]+)\ tests?,\ ([0-9]+)\ assertions? ]]; then
        TOTAL_TESTS=$((TOTAL_TESTS + BASH_REMATCH[1]))
        TOTAL_ASSERTIONS=$((TOTAL_ASSERTIONS + BASH_REMATCH[2]))
        return
    fi

    if [[ "$summary_line" =~ Tests:\ ([0-9]+),\ Assertions:\ ([0-9]+) ]]; then
        TOTAL_TESTS=$((TOTAL_TESTS + BASH_REMATCH[1]))
        TOTAL_ASSERTIONS=$((TOTAL_ASSERTIONS + BASH_REMATCH[2]))
    fi
}

worker_database_name() {
    printf '%s_%s\n' "$DB_BASE_NAME" "$1"
}

prepare_worker_databases() {
    local -a command=(bash "$ROOT_DIR/not_for_release/testFramework/prepare-worker-databases.sh" --workers "$PROCESS_COUNT" --skip-base --base "$DB_BASE_NAME")

    if [ "$DRY_RUN" -eq 1 ]; then
        command+=(--dry-run)
    fi

    ZC_TEST_DB_HOST="$DB_HOST" \
    ZC_TEST_DB_PORT="$DB_PORT" \
    ZC_TEST_DB_USER="$DB_USER" \
    ZC_TEST_DB_PASSWORD="$DB_PASSWORD" \
        "${command[@]}"
}

database_exists() {
    local database_name="$1"

    "$PHP_BIN" -r '
        $host = $argv[1];
        $port = $argv[2];
        $user = $argv[3];
        $password = $argv[4];
        $database = $argv[5];

        try {
            $pdo = new PDO(
                sprintf("mysql:host=%s;port=%s;charset=utf8mb4", $host, $port),
                $user,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $statement = $pdo->prepare("SELECT SCHEMA_NAME FROM information_schema.schemata WHERE SCHEMA_NAME = ?");
            $statement->execute([$database]);
            exit($statement->fetchColumn() === false ? 1 : 0);
        } catch (Throwable $exception) {
            fwrite(STDERR, $exception->getMessage() . PHP_EOL);
            exit(2);
        }
    ' "$DB_HOST" "$DB_PORT" "$DB_USER" "$DB_PASSWORD" "$database_name"
}

verify_worker_databases_exist() {
    local -a missing_databases=()

    for ((worker = 1; worker <= PROCESS_COUNT; worker++)); do
        local database_name
        database_name="$(worker_database_name "$worker")"

        if database_exists "$database_name"; then
            continue
        fi

        local status=$?
        if [ "$status" -eq 1 ]; then
            missing_databases+=("$database_name")
            continue
        fi

        echo "Unable to verify worker databases on ${DB_HOST}:${DB_PORT} for user ${DB_USER}." >&2
        echo "Try: ZC_TEST_DB_BASE_NAME=$DB_BASE_NAME ZC_TEST_DB_WORKERS=$PROCESS_COUNT composer test-db-prepare-workers-dry-run" >&2
        exit 1
    done

    if [ "${#missing_databases[@]}" -gt 0 ]; then
        echo "Missing worker databases: ${missing_databases[*]}" >&2
        echo "Create them with: ZC_TEST_DB_BASE_NAME=$DB_BASE_NAME ZC_TEST_DB_WORKERS=$PROCESS_COUNT ZC_TEST_DB_INCLUDE_BASE=0 composer test-db-prepare-workers" >&2
        echo "Or rerun with --prepare-databases to create them automatically." >&2
        exit 1
    fi
}

trap cleanup EXIT

for arg in "$@"; do
    case "$arg" in
        --help|-h)
            usage
            exit 0
            ;;
        --dry-run)
            DRY_RUN=1
            ;;
        --prepare-databases)
            PREPARE_DATABASES=1
            ;;
        *)
            EXTRA_PHPUNIT_ARGS+=("$arg")
            ;;
    esac
done

if [ ! -f "$PHPUNIT_BIN" ]; then
    echo "Unable to locate PHPUnit at $PHPUNIT_BIN" >&2
    exit 1
fi

if ! [[ "$PROCESS_COUNT" =~ ^[1-9][0-9]*$ ]]; then
    echo "ZC_FEATURE_PARALLEL_PROCESSES must be a positive integer." >&2
    exit 1
fi

if ! [[ "$PROGRESS_INTERVAL" =~ ^[0-9]+$ ]]; then
    echo "ZC_TEST_PROGRESS_INTERVAL must be zero or a positive integer." >&2
    exit 1
fi

for ((i = 0; i < ${#EXTRA_PHPUNIT_ARGS[@]}; i++)); do
    if [ "${EXTRA_PHPUNIT_ARGS[$i]}" = "--filter" ] && [ $((i + 1)) -lt ${#EXTRA_PHPUNIT_ARGS[@]} ]; then
        CLI_FILTER="${EXTRA_PHPUNIT_ARGS[$((i + 1))]}"
        break
    fi

    if [[ "${EXTRA_PHPUNIT_ARGS[$i]}" == --filter=* ]]; then
        CLI_FILTER="${EXTRA_PHPUNIT_ARGS[$i]#--filter=}"
        break
    fi
done

find "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort > "$TEST_LIST_FILE.all"
while IFS= read -r file; do
    if grep -q "@group parallel-candidate" "$file"; then
        printf '%s\n' "$file" >> "$TEST_LIST_FILE"
    fi
done < "$TEST_LIST_FILE.all"

if [ ! -s "$TEST_LIST_FILE" ]; then
    echo "No admin parallel-candidate feature test files were found." >&2
    exit 1
fi

if [ -n "$TEST_FILTER" ]; then
    apply_filter "$TEST_LIST_FILE" "$TEST_FILTER"
fi

if [ -n "$CLI_FILTER" ]; then
    apply_filter "$TEST_LIST_FILE" "$CLI_FILTER"
fi

if [ ! -s "$TEST_LIST_FILE" ]; then
    echo "No admin parallel-candidate feature test files matched the requested filter." >&2
    exit 1
fi

mapfile -t TEST_FILES < "$TEST_LIST_FILE"
TOTAL_FILES="${#TEST_FILES[@]}"

for ((worker = 1; worker <= PROCESS_COUNT; worker++)); do
    AVAILABLE_WORKERS+=("$worker")
done

acquire_worker() {
    ACQUIRED_WORKER="${AVAILABLE_WORKERS[0]}"
    AVAILABLE_WORKERS=("${AVAILABLE_WORKERS[@]:1}")
}

release_worker() {
    AVAILABLE_WORKERS+=("$1")
}

run_test_file() {
    local file="$1"
    local worker_token="$2"
    local relative="${file#$ROOT_DIR/}"
    local slug
    slug="$(printf "%s" "$relative" | tr "/:" "__")"
    local output_file="$WORK_DIR/$slug.log"
    local status_file="$WORK_DIR/$slug.status"

    if [ "$DRY_RUN" -eq 1 ]; then
        echo "DRY   [worker $worker_token] $relative"
        return 0
    fi

    echo "START [worker $worker_token] $relative"

    (
        export ZC_TEST_WORKER="$worker_token"

        if "$PHP_BIN" "$PHPUNIT_BIN" --configuration "$ROOT_DIR/phpunit.xml" --verbose --testsuite FeatureAdmin --group parallel-candidate "${EXTRA_PHPUNIT_ARGS[@]}" "$file" >"$output_file" 2>&1; then
            echo 0 >"$status_file"
        else
            echo $? >"$status_file"
        fi
    ) &

    local pid=$!
    PID_TO_FILE["$pid"]="$relative"
    PID_TO_SLUG["$pid"]="$slug"
    PID_TO_WORKER["$pid"]="$worker_token"
    PID_TO_STARTED_AT["$pid"]="$(date +%s)"
}

report_active_jobs() {
    local now
    now="$(date +%s)"
    local -a details=()

    for pid in "${!PID_TO_FILE[@]}"; do
        local started_at="${PID_TO_STARTED_AT[$pid]:-$now}"
        local elapsed=$((now - started_at))
        details+=("[worker ${PID_TO_WORKER[$pid]}] ${PID_TO_FILE[$pid]} (${elapsed}s)")
    done

    if [ "${#details[@]}" -eq 0 ]; then
        return
    fi

    local joined_details
    joined_details="$(printf '%s, ' "${details[@]}")"
    joined_details="${joined_details%, }"
    echo "WAIT  still running ${#details[@]} admin feature test file(s): $joined_details"
}

reap_one() {
    local finished_pid=""
    local wait_status=0
    local waited_seconds=0

    while [ -z "$finished_pid" ]; do
        for pid in "${!PID_TO_FILE[@]}"; do
            if kill -0 "$pid" 2>/dev/null; then
                continue
            fi

            finished_pid="$pid"
            if wait "$pid"; then
                wait_status=0
            else
                wait_status=$?
            fi
            break
        done

        if [ -n "$finished_pid" ]; then
            break
        fi

        sleep 1
        waited_seconds=$((waited_seconds + 1))
        if [ "$PROGRESS_INTERVAL" -gt 0 ] && [ $((waited_seconds % PROGRESS_INTERVAL)) -eq 0 ]; then
            report_active_jobs
        fi
    done

    local relative="${PID_TO_FILE[$finished_pid]}"
    local slug="${PID_TO_SLUG[$finished_pid]}"
    local worker_token="${PID_TO_WORKER[$finished_pid]}"
    local status_file="$WORK_DIR/$slug.status"
    local output_file="$WORK_DIR/$slug.log"

    unset 'PID_TO_FILE[$finished_pid]'
    unset 'PID_TO_SLUG[$finished_pid]'
    unset 'PID_TO_WORKER[$finished_pid]'
    unset 'PID_TO_STARTED_AT[$finished_pid]'

    release_worker "$worker_token"

    if [ -f "$status_file" ]; then
        wait_status="$(cat "$status_file")"
    fi

    if [ -f "$output_file" ]; then
        accumulate_phpunit_counts "$output_file"
    fi

    if [ "${wait_status:-1}" = "0" ]; then
        echo "PASS  [worker $worker_token] $relative"
        return 0
    fi

    echo "FAIL  [worker $worker_token] $relative"
    if [ -f "$output_file" ]; then
        cat "$output_file"
    else
        echo "No log output was captured for this test process."
    fi
    echo
    return 1
}

echo "Running $TOTAL_FILES admin parallel-candidate feature test file(s) in parallel with $PROCESS_COUNT worker(s)."
if [ -n "$CLI_FILTER" ]; then
    echo "CLI filter narrowed file selection using substring: $CLI_FILTER"
fi
if [ -n "$TEST_FILTER" ]; then
    echo "Env filter narrowed file selection using substring: $TEST_FILTER"
fi
echo "Worker DB base: $DB_BASE_NAME"
if [ "$PREPARE_DATABASES" -eq 1 ]; then
    echo "Worker database preparation: enabled"
    prepare_worker_databases
else
    echo "Worker database preparation: disabled"
fi

if [ "$DRY_RUN" -eq 1 ]; then
    for ((index = 0; index < TOTAL_FILES; index++)); do
        file="${TEST_FILES[$index]}"
        worker_token=$(( (index % PROCESS_COUNT) + 1 ))
        run_test_file "$file" "$worker_token"
    done
    exit 0
fi

verify_worker_databases_exist

failures=0

for file in "${TEST_FILES[@]}"; do
    while [ "${#PID_TO_FILE[@]}" -ge "$PROCESS_COUNT" ]; do
        if ! reap_one; then
            failures=$((failures + 1))
        fi
    done

    acquire_worker
    run_test_file "$file" "$ACQUIRED_WORKER"
done

while [ "${#PID_TO_FILE[@]}" -gt 0 ]; do
    if ! reap_one; then
        failures=$((failures + 1))
    fi
done

if [ "$failures" -gt 0 ]; then
    echo "Admin parallel-candidate summary: $failures failing file(s), $TOTAL_TESTS test(s), $TOTAL_ASSERTIONS assertion(s)." >&2
    echo "$failures admin feature test file(s) failed." >&2
    exit 1
fi

echo "Admin parallel-candidate summary: $failures failing file(s), $TOTAL_TESTS test(s), $TOTAL_ASSERTIONS assertion(s)."
echo "All admin parallel-candidate feature test files passed."
