#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DRY_RUN=0
declare -a FEATURE_ARGS=()
CLI_FILTER=""

suite_has_matches() {
    local suite_dir="$1"
    local required_group="$2"
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

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [feature-runner-args...]

Runs the admin feature-test CI flow:
  1. worker runtime description
  2. strict feature test grouping report
  3. worker database preparation (or dry-run preview)
  4. admin parallel feature runner
  5. admin plugin-filesystem serial bucket

Examples:
  composer feature-tests-admin-ci -- --filter AdminEndpointsTest
  composer feature-tests-admin-ci-dry-run -- --filter BasicPluginInstallTest
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
    bash "$ROOT_DIR/not_for_release/testFramework/run-parallel-admin-feature-tests.sh" "${FEATURE_ARGS[@]}"
else
    echo "SKIP  [admin] no matching admin parallel-candidate files"
fi

if suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$CLI_FILTER"; then
    if [ "$DRY_RUN" -eq 1 ]; then
    echo "RUN   [admin-plugin] feature-tests-admin-plugin-filesystem (dry run)"
    while IFS= read -r file; do
        name="${file##*/}"
        stem="${name%.php}"
        if [ -n "$CLI_FILTER" ] && [ "$name" != "$CLI_FILTER" ] && [ "$stem" != "$CLI_FILTER" ] && [[ "$file" != *"$CLI_FILTER"* ]]; then
            continue
        fi
        echo "DRY   [admin-plugin] ${file#$ROOT_DIR/}"
    done < <(find "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort | xargs grep -l "@group plugin-filesystem")
    else
        if [ -n "$CLI_FILTER" ]; then
        composer feature-tests-admin-plugin-filesystem -- --filter "$CLI_FILTER"
        else
        composer feature-tests-admin-plugin-filesystem
        fi
    fi
else
    echo "SKIP  [admin-plugin] no matching admin plugin-filesystem files"
fi

if ! suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "parallel-candidate" "$CLI_FILTER" && ! suite_has_matches "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" "plugin-filesystem" "$CLI_FILTER"; then
    echo "No admin feature test files matched the requested filter." >&2
    exit 1
fi
