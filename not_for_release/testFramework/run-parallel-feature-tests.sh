#!/usr/bin/env bash

set -uo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
TEST_FILTER="${ZC_FEATURE_TEST_FILTER:-}"
CLI_FILTER=""
PREPARE_DATABASES=0
DRY_RUN=0
PHPUNIT_BIN="${PHPUNIT_BIN:-$ROOT_DIR/vendor/bin/phpunit}"
declare -a EXTRA_ARGS=()

file_has_group() {
    local file="$1"
    local group="$2"

    grep -Eq "^[[:space:]]*\*[[:space:]]+@group[[:space:]]+${group}([[:space:]]|$)" "$file" \
        || grep -Eq "^[[:space:]]*#\[[^]]*Group\(['\"]${group}['\"]\)\]" "$file"
}

matches_filter() {
    local file="$1"
    local requested_filter="$2"

    if [ -z "$requested_filter" ]; then
        return 0
    fi

    local name="${file##*/}"
    local stem="${name%.php}"
    [ "$name" = "$requested_filter" ] || [ "$stem" = "$requested_filter" ] || [[ "$file" == *"$requested_filter"* ]]
}

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [--prepare-databases] [phpunit-args...]

Runs the storefront/admin parallel feature runners plus the remaining
admin serial and plugin-filesystem serial buckets, skipping buckets that do not match the
requested filter.

Examples:
  composer tests-feature-parallel -- --dry-run
  composer tests-feature-parallel -- --filter AdminEndpointsTest
  ZC_TEST_DB_BASE_NAME=db ZC_TEST_DB_WORKERS=2 ZC_TEST_DB_INCLUDE_BASE=0 composer tests-feature-parallel -- --prepare-databases --filter SearchInProcessTest
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
        if ! file_has_group "$file" "$required_group"; then
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
    bash "$script" "${EXTRA_ARGS[@]+"${EXTRA_ARGS[@]}"}"
}

list_plugin_suite_matches() {
    local requested_filter="$1"

    while IFS= read -r file; do
        if ! file_has_group "$file" "plugin-filesystem"; then
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

list_admin_serial_suite_matches() {
    local requested_filter="$1"

    while IFS= read -r file; do
        if ! file_has_group "$file" "serial"; then
            continue
        fi

        if file_has_group "$file" "plugin-filesystem"; then
            continue
        fi

        if ! matches_filter "$file" "$requested_filter"; then
            continue
        fi

        printf '%s\n' "$file"
    done < <(find "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort)
}

list_plugin_local_suite_matches() {
    local requested_filter="$1"

    if [ ! -d "$ROOT_DIR/zc_plugins" ]; then
        return
    fi

    while IFS= read -r file; do
        if ! file_has_group "$file" "plugin-filesystem"; then
            continue
        fi

        local name="${file##*/}"
        local stem="${name%.php}"
        if [ -n "$requested_filter" ] && [ "$name" != "$requested_filter" ] && [ "$stem" != "$requested_filter" ] && [[ "$file" != *"$requested_filter"* ]]; then
            continue
        fi

        printf '%s\n' "$file"
    done < <(find "$ROOT_DIR/zc_plugins" \( -path '*/tests/FeatureAdmin/*Test.php' -o -path '*/tests/FeatureStore/*Test.php' \) -type f | sort)
}

plugin_local_suite_has_matches() {
    local requested_filter="$1"
    local first_match

    first_match="$(list_plugin_local_suite_matches "$requested_filter" | sed -n '1p')"
    [ -n "$first_match" ]
}

admin_serial_suite_has_matches() {
    local requested_filter="$1"
    local first_match

    first_match="$(list_admin_serial_suite_matches "$requested_filter" | sed -n '1p')"
    [ -n "$first_match" ]
}

print_plugin_suite_dry_run_matches() {
    while IFS= read -r file; do
        [ -n "$file" ] || continue
        echo "DRY   [admin-plugin] ${file#$ROOT_DIR/}"
    done < <(list_plugin_suite_matches "$1")
}

print_admin_serial_dry_run_matches() {
    while IFS= read -r file; do
        [ -n "$file" ] || continue
        echo "DRY   [admin-serial] ${file#$ROOT_DIR/}"
    done < <(list_admin_serial_suite_matches "$1")
}

print_plugin_local_dry_run_matches() {
    while IFS= read -r file; do
        [ -n "$file" ] || continue
        echo "DRY   [plugin-local] ${file#$ROOT_DIR/}"
    done < <(list_plugin_local_suite_matches "$1")
}

run_plugin_suite() {
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "RUN   [admin-plugin] tests-feature-admin-plugin-filesystem (dry run)"
        print_plugin_suite_dry_run_matches "$effective_filter"
        return 0
    fi

    echo "RUN   [admin-plugin] tests-feature-admin-plugin-filesystem"

    local -a command=(composer tests-feature-admin-plugin-filesystem)
    if [ -n "$CLI_FILTER" ]; then
        command+=(-- --filter "$CLI_FILTER")
    elif [ -n "$TEST_FILTER" ]; then
        command+=(-- --filter "$TEST_FILTER")
    fi

    "${command[@]}"
}

run_admin_serial_suite() {
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "RUN   [admin-serial] phpunit serial bucket (dry run)"
        print_admin_serial_dry_run_matches "$effective_filter"
        return 0
    fi

    while IFS= read -r file; do
        [ -n "$file" ] || continue
        echo "RUN   [admin-serial] ${file#$ROOT_DIR/}"
        "$PHPUNIT_BIN" --configuration "$ROOT_DIR/phpunit.xml" --testsuite FeatureAdmin --group serial --exclude-group plugin-filesystem "${EXTRA_ARGS[@]+"${EXTRA_ARGS[@]}"}" "$file"
    done < <(list_admin_serial_suite_matches "$effective_filter")
}

run_plugin_local_suite() {
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "RUN   [plugin-local] tests-plugin plugin-filesystem (dry run)"
        print_plugin_local_dry_run_matches "$effective_filter"
        return 0
    fi

    echo "RUN   [plugin-local] tests-plugin plugin-filesystem"

    local -a command=(bash "$ROOT_DIR/not_for_release/testFramework/run-plugin-tests.sh" --require-group plugin-filesystem --group plugin-filesystem)
    if [ -n "$CLI_FILTER" ]; then
        command+=(--filter "$CLI_FILTER")
    elif [ -n "$TEST_FILTER" ]; then
        command+=(--filter "$TEST_FILTER")
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

if admin_serial_suite_has_matches "$effective_filter"; then
    run_admin_serial_suite
    ran_any=1
else
    echo "SKIP  [admin-serial] no matching admin serial files"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$effective_filter"; then
    run_plugin_suite
    ran_any=1
else
    echo "SKIP  [admin-plugin] no matching admin plugin-filesystem files"
fi

if plugin_local_suite_has_matches "$effective_filter"; then
    run_plugin_local_suite
    ran_any=1
else
    echo "SKIP  [plugin-local] no matching plugin-local plugin-filesystem files"
fi

if [ "$ran_any" -eq 0 ]; then
    echo "No feature test files matched the requested filter." >&2
    exit 1
fi

if [ "$PREPARE_DATABASES" -eq 1 ] || [ "$DRY_RUN" -eq 1 ]; then
    exit 0
fi

echo "Parallel feature test aggregate completed."
