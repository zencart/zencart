#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DRY_RUN=0
CLI_FILTER=""
UNIT_FILTER="${ZC_UNIT_TEST_FILTER:-}"
declare -a TEST_ARGS=()
declare -a UNIT_ARGS=()

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [test-runner-args...]

Runs the top-level CI-style test flow:
  1. unit test CI entrypoint
  2. feature test CI entrypoint

The wrapper skips the unit lane when the requested filter only matches feature
tests, so targeted feature dry-runs do not fail on unit no-match errors.

Examples:
  composer tests-ci
  composer tests-ci-local
  composer tests-ci-dry-run -- --filter BasicPluginInstallTest
  composer tests-ci-local-dry-run -- --filter SearchInProcessTest
EOF
}

extract_cli_filter() {
    for ((i = 0; i < ${#TEST_ARGS[@]}; i++)); do
        if [ "${TEST_ARGS[$i]}" = "--filter" ] && [ $((i + 1)) -lt ${#TEST_ARGS[@]} ]; then
            CLI_FILTER="${TEST_ARGS[$((i + 1))]}"
            return
        fi

        if [[ "${TEST_ARGS[$i]}" == --filter=* ]]; then
            CLI_FILTER="${TEST_ARGS[$i]#--filter=}"
            return
        fi
    done
}

build_unit_args() {
    UNIT_ARGS=()

    for arg in "${TEST_ARGS[@]}"; do
        if [ "$arg" = "--dry-run" ]; then
            continue
        fi

        UNIT_ARGS+=("$arg")
    done
}

unit_suite_has_matches() {
    local requested_filter="$1"
    local env_filter="$2"

    while IFS= read -r file; do
        local matched=1
        local name="${file##*/}"
        local stem="${name%.php}"

        if [ -n "$requested_filter" ]; then
            if [ "$name" = "$requested_filter" ] || [ "$stem" = "$requested_filter" ] || [[ "$file" == *"$requested_filter"* ]]; then
                matched=0
            fi
        else
            matched=0
        fi

        if [ "$matched" -eq 0 ] && [ -n "$env_filter" ]; then
            if [[ "$file" != *"$env_filter"* ]]; then
                matched=1
            fi
        fi

        if [ "$matched" -eq 0 ]; then
            return 0
        fi
    done < <(find "$ROOT_DIR/not_for_release/testFramework/Unit" -type f -name '*Test.php' | sort)

    return 1
}

feature_suite_has_matches() {
    local requested_filter="$1"

    while IFS= read -r file; do
        local matched=1
        local name="${file##*/}"
        local stem="${name%.php}"

        if [ -n "$requested_filter" ]; then
            if [ "$name" = "$requested_filter" ] || [ "$stem" = "$requested_filter" ] || [[ "$file" == *"$requested_filter"* ]]; then
                matched=0
            fi
        else
            matched=0
        fi

        if [ "$matched" -eq 0 ]; then
            return 0
        fi
    done < <(find "$ROOT_DIR/not_for_release/testFramework/FeatureStore" "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" -type f -name '*Test.php' | sort)

    return 1
}

for arg in "$@"; do
    case "$arg" in
        --help|-h)
            usage
            exit 0
            ;;
        --dry-run)
            DRY_RUN=1
            TEST_ARGS+=("$arg")
            ;;
        *)
            TEST_ARGS+=("$arg")
            ;;
    esac
done

extract_cli_filter
build_unit_args

run_unit=0
if [ -n "$CLI_FILTER" ] || [ -n "$UNIT_FILTER" ]; then
    if unit_suite_has_matches "$CLI_FILTER" "$UNIT_FILTER"; then
        run_unit=0
    else
        run_unit=1
    fi
fi

run_feature=0
if [ -n "$CLI_FILTER" ]; then
    if feature_suite_has_matches "$CLI_FILTER"; then
        run_feature=0
    else
        run_feature=1
    fi
fi

if [ "$run_unit" -eq 0 ]; then
    bash "$ROOT_DIR/not_for_release/testFramework/run-parallel-unit-tests.sh" "${UNIT_ARGS[@]}"
else
    echo "SKIP  [unit] no matching unit test files"
fi

if [ "$DRY_RUN" -eq 1 ]; then
    if [ "$run_feature" -eq 0 ]; then
        bash "$ROOT_DIR/not_for_release/testFramework/run-feature-tests-ci.sh" --dry-run "${TEST_ARGS[@]}"
    else
        echo "SKIP  [feature] no matching feature test files"
    fi
else
    if [ "$run_feature" -eq 0 ]; then
        bash "$ROOT_DIR/not_for_release/testFramework/run-feature-tests-ci.sh" "${TEST_ARGS[@]}"
    else
        echo "SKIP  [feature] no matching feature test files"
    fi
fi

if [ "$run_unit" -ne 0 ] && [ "$run_feature" -ne 0 ]; then
    echo "No unit or feature test files matched the requested filter." >&2
    exit 1
fi
