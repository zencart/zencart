#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
. "$ROOT_DIR/not_for_release/testFramework/load-test-environment.sh"
load_test_framework_env "$ROOT_DIR"
DRY_RUN=0
declare -a FEATURE_ARGS=()
CLI_FILTER=""
PHPUNIT_BIN="${PHPUNIT_BIN:-$ROOT_DIR/vendor/bin/phpunit}"

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

list_plain_serial_matches() {
    local suite_dir="$1"
    local requested_filter="$2"

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
    done < <(find "$suite_dir" -type f -name '*Test.php' | sort)
}

plain_serial_suite_has_matches() {
    local suite_dir="$1"
    local requested_filter="$2"
    local first_match

    first_match="$(list_plain_serial_matches "$suite_dir" "$requested_filter" | sed -n '1p')"
    [ -n "$first_match" ]
}

suite_has_matches() {
    local suite_dir="$1"
    local required_group="$2"
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

plugin_local_suite_has_matches() {
    local requested_filter="$1"
    local found_any=1

    if [ ! -d "$ROOT_DIR/zc_plugins" ]; then
        return "$found_any"
    fi

    while IFS= read -r file; do
        if ! file_has_group "$file" "plugin-filesystem"; then
            continue
        fi

        local name="${file##*/}"
        local stem="${name%.php}"
        if [ -z "$requested_filter" ] || [ "$name" = "$requested_filter" ] || [ "$stem" = "$requested_filter" ] || [[ "$file" == *"$requested_filter"* ]]; then
            found_any=0
            break
        fi
    done < <(find "$ROOT_DIR/zc_plugins" -path '*/tests/FeatureAdmin/*Test.php' -type f | sort)

    return "$found_any"
}

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [feature-runner-args...]

Runs the admin feature-test CI flow:
  1. worker runtime description
  2. strict feature test grouping report
  3. worker database preparation (or dry-run preview)
  4. admin parallel feature runner
  5. admin serial bucket
  6. admin plugin-filesystem serial bucket

Examples:
  composer tests-feature-admin -- --filter AdminEndpointsTest
  composer tests-feature-admin -- --dry-run --filter BasicPluginInstallTest
  ZC_TEST_DB_BASE_NAME=db ZC_TEST_DB_WORKERS=2 ZC_TEST_DB_INCLUDE_BASE=0 bash not_for_release/testFramework/run-admin-feature-tests-ci.sh --filter BasicPluginInstallTest
EOF
}

for arg in "$@"; do
    case "$arg" in
        --help|-h)
            usage
            exit 0
            ;;
        --dry-run)
            DRY_RUN=1
            FEATURE_ARGS+=("$arg")
            ;;
        *)
            FEATURE_ARGS+=("$arg")
            ;;
    esac
done

for ((i = 0; i < ${#FEATURE_ARGS[@]}; i++)); do
    if [ "${FEATURE_ARGS[$i]}" = "--filter" ] && [ $((i + 1)) -lt ${#FEATURE_ARGS[@]} ]; then
        CLI_FILTER="${FEATURE_ARGS[$((i + 1))]}"
        break
    fi

    if [[ "${FEATURE_ARGS[$i]}" == --filter=* ]]; then
        CLI_FILTER="${FEATURE_ARGS[$i]#--filter=}"
        break
    fi
done

php "$ROOT_DIR/not_for_release/testFramework/describe-worker-runtime.php"
bash "$ROOT_DIR/not_for_release/testFramework/report-feature-test-groups.sh" --fail-on-untagged --summary-only

if [ "$DRY_RUN" -eq 1 ]; then
    bash "$ROOT_DIR/not_for_release/testFramework/prepare-worker-databases.sh" --dry-run
else
    bash "$ROOT_DIR/not_for_release/testFramework/prepare-worker-databases.sh"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "parallel-candidate" "$CLI_FILTER"; then
    bash "$ROOT_DIR/not_for_release/testFramework/run-parallel-admin-feature-tests.sh" "${FEATURE_ARGS[@]+"${FEATURE_ARGS[@]}"}"
else
    echo "SKIP  [admin] no matching admin parallel-candidate files"
fi

if plain_serial_suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "$CLI_FILTER"; then
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "RUN   [admin-serial] phpunit serial bucket (dry run)"
        while IFS= read -r file; do
            [ -n "$file" ] || continue
            echo "DRY   [admin-serial] ${file#$ROOT_DIR/}"
        done < <(list_plain_serial_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "$CLI_FILTER")
    else
        while IFS= read -r file; do
            [ -n "$file" ] || continue
            echo "RUN   [admin-serial] ${file#$ROOT_DIR/}"
            "$PHPUNIT_BIN" --configuration "$ROOT_DIR/phpunit.xml" --testsuite FeatureAdmin --group serial --exclude-group plugin-filesystem "${FEATURE_ARGS[@]+"${FEATURE_ARGS[@]}"}" "$file"
        done < <(list_plain_serial_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "$CLI_FILTER")
    fi
else
    echo "SKIP  [admin-serial] no matching admin serial files"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$CLI_FILTER"; then
    if [ "$DRY_RUN" -eq 1 ]; then
    echo "RUN   [admin-plugin] tests-feature-admin-plugin-filesystem (dry run)"
    while IFS= read -r file; do
        name="${file##*/}"
        stem="${name%.php}"
        if [ -n "$CLI_FILTER" ] && [ "$name" != "$CLI_FILTER" ] && [ "$stem" != "$CLI_FILTER" ] && [[ "$file" != *"$CLI_FILTER"* ]]; then
            continue
        fi
        echo "DRY   [admin-plugin] ${file#$ROOT_DIR/}"
    done < <(
        while IFS= read -r file; do
            if file_has_group "$file" "plugin-filesystem"; then
                printf '%s\n' "$file"
            fi
        done < <(find "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort)
    )
    else
        if [ -n "$CLI_FILTER" ]; then
        composer tests-feature-admin-plugin-filesystem -- --filter "$CLI_FILTER"
        else
        composer tests-feature-admin-plugin-filesystem
        fi
    fi
else
    echo "SKIP  [admin-plugin] no matching admin plugin-filesystem files"
fi

if plugin_local_suite_has_matches "$CLI_FILTER"; then
    if [ "$DRY_RUN" -eq 1 ]; then
        bash "$ROOT_DIR/not_for_release/testFramework/run-plugin-tests.sh" --dry-run --suite FeatureAdmin --require-group plugin-filesystem --group plugin-filesystem "${FEATURE_ARGS[@]+"${FEATURE_ARGS[@]}"}"
    else
        bash "$ROOT_DIR/not_for_release/testFramework/run-plugin-tests.sh" --suite FeatureAdmin --require-group plugin-filesystem --group plugin-filesystem "${FEATURE_ARGS[@]+"${FEATURE_ARGS[@]}"}"
    fi
else
    echo "SKIP  [plugin-local] no matching plugin-local plugin-filesystem files"
fi

if ! suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "parallel-candidate" "$CLI_FILTER" && ! plain_serial_suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "$CLI_FILTER" && ! suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$CLI_FILTER" && ! plugin_local_suite_has_matches "$CLI_FILTER"; then
    echo "No admin feature test files matched the requested filter." >&2
    exit 1
fi
