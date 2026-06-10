#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
declare -a FORWARDED_ARGS=()

while [ "$#" -gt 0 ]; do
    case "$1" in
        --base)
            export ZC_TEST_DB_BASE_NAME="${2:-}"
            shift
            ;;
        --workers)
            export ZC_TEST_DB_WORKERS="${2:-}"
            shift
            ;;
        --skip-base)
            export ZC_TEST_DB_INCLUDE_BASE="0"
            ;;
        --include-base)
            export ZC_TEST_DB_INCLUDE_BASE="1"
            ;;
        *)
            FORWARDED_ARGS+=("$1")
            ;;
    esac
    shift
done

if [ -z "${ZC_TEST_DB_BASE_NAME:-}" ]; then
    export ZC_TEST_DB_BASE_NAME="db"
fi

if [ -z "${ZC_TEST_DB_WORKERS:-}" ]; then
    export ZC_TEST_DB_WORKERS="2"
fi

if [ -z "${ZC_TEST_DB_INCLUDE_BASE:-}" ]; then
    export ZC_TEST_DB_INCLUDE_BASE="0"
fi

bash "$ROOT_DIR/not_for_release/testFramework/run-feature-tests-ci.sh" "${FORWARDED_ARGS[@]}"
