#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TEST_FILTER="${ZC_FEATURE_TEST_FILTER:-}"
CLI_FILTER=""
PREPARE_DATABASES=0
DRY_RUN=0
declare -a EXTRA_ARGS=()

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [--prepare-databases] [phpunit-args...]

Runs the storefront/admin parallel feature runners plus the remaining
plugin-filesystem serial admin bucket, skipping buckets that do not match the
requested filter.

Examples:
  composer feature-tests-parallel -- --dry-run
  composer feature-tests-parallel -- --filter AdminEndpointsTest
  composer feature-tests-parallel-local -- --filter SearchInProcessTest
EOF
}

extract_cli_filter() {
    for ((i = 0; i < ${#EXTRA_ARGS[@]}; i++)); do
        if [ "${EXTRA_ARGS[$i]}" = "--filter" ] && [ $((i + 1)) -lt ${#EXTRA_ARGS[@]} ]; then
            CLI_FILTER="${EXTRA_ARGS[$((i + 1))]}"
            return
        fi

        if [[ "${EXTRA_ARGS[$i]}" == --filter=* ]]; then
            CLI_FILTER="${EXTRA_ARGS[$i]#--filter=}"
            return
        fi
    done
}

suite_has_matches() {
    local suite_dir="$1"
    local required_group="${2:-parallel-candidate}"
    local requested_filter="$3"
    local found_any=1

    while IFS= read -r file; do
        if ! grep -q "@group ${required_group}" "$file"; then
            continue
        fi

        if [ -z "$requested_filter" ]; then
            found_any=0
            break
        fi

        local name="${file##*/}"
        local stem="${name%.php}"
        if [ "$name" = "$requested_filter" ] || [ "$stem" = "$requested_filter" ] || [[ "$file" == *"$requested_filter"* ]]; then
            found_any=0
            break
        fi
    done < <(find "$suite_dir" -type f -name '*Test.php' | sort)

    return "$found_any"
}

run_script_suite() {
    local label="$1"
    local script="$2"

    echo "RUN   [$label] $(basename "$script")"
    bash "$script" "${EXTRA_ARGS[@]}"
}

list_plugin_suite_matches() {
    local requested_filter="$1"

    while IFS= read -r file; do
        if ! grep -q "@group plugin-filesystem" "$file"; then
            continue
        fi

        local name="${file##*/}"
        local stem="${name%.php}"
        if [ -n "$requested_filter" ] && [ "$name" != "$requested_filter" ] && [ "$stem" != "$requested_filter" ] && [[ "$file" != *"$requested_filter"* ]]; then
            continue
        fi

        printf '%s\n' "$file"
    done < <(find "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort)
}

run_plugin_suite() {
    if [ "$DRY_RUN" -eq 1 ]; then
        mapfile -t plugin_matches < <(list_plugin_suite_matches "$effective_filter")

        echo "RUN   [admin-plugin] feature-tests-admin-plugin-filesystem (dry run)"
        for file in "${plugin_matches[@]}"; do
            echo "DRY   [admin-plugin] ${file#$ROOT_DIR/}"
        done
        return 0
    fi

    echo "RUN   [admin-plugin] feature-tests-admin-plugin-filesystem"

    local -a command=(composer feature-tests-admin-plugin-filesystem)
    if [ -n "$CLI_FILTER" ]; then
        command+=(-- --filter "$CLI_FILTER")
    elif [ -n "$TEST_FILTER" ]; then
        command+=(-- --filter "$TEST_FILTER")
    fi

    "${command[@]}"
}

for arg in "$@"; do
    case "$arg" in
        --help|-h)
            usage
            exit 0
            ;;
        --prepare-databases)
            PREPARE_DATABASES=1
            EXTRA_ARGS+=("$arg")
            ;;
        --dry-run)
            DRY_RUN=1
            EXTRA_ARGS+=("$arg")
            ;;
        *)
            EXTRA_ARGS+=("$arg")
            ;;
    esac
done

extract_cli_filter

effective_filter="$CLI_FILTER"
if [ -z "$effective_filter" ]; then
    effective_filter="$TEST_FILTER"
fi

store_script="$ROOT_DIR/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh"
admin_script="$ROOT_DIR/not_for_release/testFramework/run-parallel-admin-feature-tests.sh"

ran_any=0

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureStore" "parallel-candidate" "$effective_filter"; then
    run_script_suite "store" "$store_script"
    ran_any=1
else
    echo "SKIP  [store] no matching storefront parallel-candidate files"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "parallel-candidate" "$effective_filter"; then
    run_script_suite "admin" "$admin_script"
    ran_any=1
else
    echo "SKIP  [admin] no matching admin parallel-candidate files"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$effective_filter"; then
    run_plugin_suite
    ran_any=1
else
    echo "SKIP  [admin-plugin] no matching admin plugin-filesystem files"
fi

if [ "$ran_any" -eq 0 ]; then
    echo "No feature test files matched the requested filter." >&2
    exit 1
fi

if [ "$PREPARE_DATABASES" -eq 1 ] || [ "$DRY_RUN" -eq 1 ]; then
    exit 0
fi

echo "Parallel feature test aggregate completed."
