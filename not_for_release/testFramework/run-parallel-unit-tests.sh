#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHP_BIN="${PHP_BIN:-php}"
PHPUNIT_BIN="${PHPUNIT_BIN:-$ROOT_DIR/vendor/bin/phpunit}"
PROCESS_COUNT="${ZC_PARALLEL_PROCESSES:-4}"
PROGRESS_INTERVAL="${ZC_TEST_PROGRESS_INTERVAL:-15}"
WORK_DIR="$(mktemp -d "${TMPDIR:-/tmp}/zc-unit-parallel.XXXXXX")"
TEST_LIST_FILE="$WORK_DIR/test-files.txt"
TEST_FILTER="${ZC_UNIT_TEST_FILTER:-}"
declare -a EXTRA_PHPUNIT_ARGS=("$@")
CLI_FILTER=""

declare -A PID_TO_FILE=()
declare -A PID_TO_SLUG=()
declare -A PID_TO_STARTED_AT=()
TOTAL_TESTS=0
TOTAL_ASSERTIONS=0

usage() {
    cat <<EOF
Usage: $(basename "$0") [phpunit-args...]

Runs unit test files in parallel by launching one PHPUnit process per file.

Useful environment variables:
  ZC_PARALLEL_PROCESSES   Number of worker processes (default: 4)
  ZC_UNIT_TEST_FILTER     Substring filter applied to unit test file paths before launch
  ZC_TEST_PROGRESS_INTERVAL
                         Seconds between in-flight progress updates while waiting (default: 15)
  PHP_BIN                 PHP executable to use (default: php)
  PHPUNIT_BIN             PHPUnit binary to use (default: vendor/bin/phpunit)

Examples:
  composer unit-tests-parallel
  composer unit-tests-parallel -- --filter RuntimeConfigTest
  ZC_UNIT_TEST_FILTER=RuntimeConfig composer unit-tests-parallel
EOF
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

trap cleanup EXIT

for arg in "${EXTRA_PHPUNIT_ARGS[@]}"; do
    case "$arg" in
        --help|-h)
            usage
            exit 0
            ;;
    esac
done

if [ ! -f "$PHPUNIT_BIN" ]; then
    echo "Unable to locate PHPUnit at $PHPUNIT_BIN" >&2
    exit 1
fi

if ! [[ "$PROCESS_COUNT" =~ ^[1-9][0-9]*$ ]]; then
    echo "ZC_PARALLEL_PROCESSES must be a positive integer." >&2
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

find "$ROOT_DIR/not_for_release/testFramework/Unit" -type f -name '*Test.php' | sort > "$TEST_LIST_FILE"

if [ ! -s "$TEST_LIST_FILE" ]; then
    echo "No unit test files were found." >&2
    exit 1
fi

if [ -n "$TEST_FILTER" ]; then
    grep -F "$TEST_FILTER" "$TEST_LIST_FILE" > "$TEST_LIST_FILE.filtered" || true
    mv "$TEST_LIST_FILE.filtered" "$TEST_LIST_FILE"
fi

if [ -n "$CLI_FILTER" ]; then
    grep -F "$CLI_FILTER" "$TEST_LIST_FILE" > "$TEST_LIST_FILE.filtered" || true
    mv "$TEST_LIST_FILE.filtered" "$TEST_LIST_FILE"
fi

if [ ! -s "$TEST_LIST_FILE" ]; then
    echo "No unit test files matched the requested filter." >&2
    exit 1
fi

mapfile -t TEST_FILES < "$TEST_LIST_FILE"
TOTAL_FILES="${#TEST_FILES[@]}"

run_test_file() {
    local file="$1"
    local relative="${file#$ROOT_DIR/}"
    local slug
    slug="$(printf "%s" "$relative" | tr "/:" "__")"
    local output_file="$WORK_DIR/$slug.log"
    local status_file="$WORK_DIR/$slug.status"
    local class_name

    class_name="$(sed -nE 's/^[[:space:]]*class[[:space:]]+([A-Za-z_][A-Za-z0-9_]*)[[:space:]].*/\1/p' "$file" | head -n 1)"

    echo "START $relative"

    (
        if [ -n "$class_name" ]; then
            if "$PHP_BIN" "$PHPUNIT_BIN" --configuration "$ROOT_DIR/phpunit.xml" --verbose --process-isolation --debug --testsuite Unit "${EXTRA_PHPUNIT_ARGS[@]}" --filter "${class_name}" >"$output_file" 2>&1; then
                echo 0 >"$status_file"
            else
                echo $? >"$status_file"
            fi
        elif "$PHP_BIN" "$PHPUNIT_BIN" --configuration "$ROOT_DIR/phpunit.xml" --verbose --process-isolation --debug "${EXTRA_PHPUNIT_ARGS[@]}" "$file" >"$output_file" 2>&1; then
            echo 0 >"$status_file"
        else
            echo $? >"$status_file"
        fi
    ) &

    local pid=$!
    PID_TO_FILE["$pid"]="$relative"
    PID_TO_SLUG["$pid"]="$slug"
    PID_TO_STARTED_AT["$pid"]="$(date +%s)"
}

report_active_jobs() {
    local now
    now="$(date +%s)"
    local -a details=()

    for pid in "${!PID_TO_FILE[@]}"; do
        local started_at="${PID_TO_STARTED_AT[$pid]:-$now}"
        local elapsed=$((now - started_at))
        details+=("${PID_TO_FILE[$pid]} (${elapsed}s)")
    done

    if [ "${#details[@]}" -eq 0 ]; then
        return
    fi

    local joined_details
    joined_details="$(printf '%s, ' "${details[@]}")"
    joined_details="${joined_details%, }"
    echo "WAIT  still running ${#details[@]} unit test file(s): $joined_details"
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
    local status_file="$WORK_DIR/$slug.status"
    local output_file="$WORK_DIR/$slug.log"

    unset 'PID_TO_FILE[$finished_pid]'
    unset 'PID_TO_SLUG[$finished_pid]'
    unset 'PID_TO_STARTED_AT[$finished_pid]'

    if [ -f "$status_file" ]; then
        wait_status="$(cat "$status_file")"
    fi

    if [ -f "$output_file" ]; then
        accumulate_phpunit_counts "$output_file"
    fi

    if [ "${wait_status:-1}" = "0" ]; then
        echo "PASS  $relative"
        return 0
    fi

    echo "FAIL  $relative"
    if [ -f "$output_file" ]; then
        cat "$output_file"
    else
        echo "No log output was captured for this test process."
    fi
    echo

    return 1
}

echo "Running $TOTAL_FILES unit test files in parallel with $PROCESS_COUNT worker(s)."
if [ -n "$CLI_FILTER" ]; then
    echo "CLI filter narrowed file selection using substring: $CLI_FILTER"
fi
if [ -n "$TEST_FILTER" ]; then
    echo "Env filter narrowed file selection using substring: $TEST_FILTER"
fi
echo

export ROOT_DIR PHP_BIN PHPUNIT_BIN WORK_DIR

failures=0
active_jobs=0

for file in "${TEST_FILES[@]}"; do
    if [ "$active_jobs" -ge "$PROCESS_COUNT" ]; then
        if ! reap_one; then
            failures=$((failures + 1))
        fi
        active_jobs=$((active_jobs - 1))
    fi

    run_test_file "$file"
    active_jobs=$((active_jobs + 1))
done

while [ "$active_jobs" -gt 0 ]; do
    if ! reap_one; then
        failures=$((failures + 1))
    fi
    active_jobs=$((active_jobs - 1))
done

echo
echo "Parallel unit test summary: $failures failing file(s), $TOTAL_TESTS test(s), $TOTAL_ASSERTIONS assertion(s)."

if [ "$failures" -gt 0 ]; then
    exit 1
fi
