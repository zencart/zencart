#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
# shellcheck source=/dev/null
. "$ROOT_DIR/not_for_release/testFramework/load-test-environment.sh"
load_test_framework_env "$ROOT_DIR"
DRY_RUN=0
declare -a FEATURE_ARGS=()

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [feature-runner-args...]

Runs the storefront feature-test CI flow:
  1. worker runtime description
  2. strict feature test grouping report
  3. worker database preparation (or dry-run preview)
  4. storefront parallel feature runner

Examples:
  composer tests-feature-store -- --filter SearchInProcessTest
  composer tests-feature-store -- --dry-run --filter SearchInProcessTest
  ZC_TEST_DB_BASE_NAME=db ZC_TEST_DB_WORKERS=2 ZC_TEST_DB_INCLUDE_BASE=0 bash not_for_release/testFramework/run-store-feature-tests-ci.sh --filter SearchInProcessTest
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

php "$ROOT_DIR/not_for_release/testFramework/describe-worker-runtime.php"
bash "$ROOT_DIR/not_for_release/testFramework/report-feature-test-groups.sh" --fail-on-untagged --summary-only

if [ "$DRY_RUN" -eq 1 ]; then
    bash "$ROOT_DIR/not_for_release/testFramework/prepare-worker-databases.sh" --dry-run
else
    bash "$ROOT_DIR/not_for_release/testFramework/prepare-worker-databases.sh"
fi

bash "$ROOT_DIR/not_for_release/testFramework/run-parallel-storefront-feature-tests.sh" "${FEATURE_ARGS[@]+"${FEATURE_ARGS[@]}"}"
