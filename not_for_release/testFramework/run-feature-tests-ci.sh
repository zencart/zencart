#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DRY_RUN=0
declare -a FEATURE_ARGS=()

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [feature-runner-args...]

Runs the aggregate feature-test CI flow:
  1. worker runtime description
  2. strict feature test grouping report
  3. worker database preparation (or dry-run preview)
  4. aggregate feature runner

Examples:
  composer feature-tests-ci -- --filter SearchInProcessTest
  composer feature-tests-ci-dry-run -- --filter BasicPluginInstallTest
  composer feature-tests-ci-local
  composer feature-tests-ci-local-dry-run -- --filter AdminEndpointsTest
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

bash "$ROOT_DIR/not_for_release/testFramework/run-parallel-feature-tests.sh" "${FEATURE_ARGS[@]}"
