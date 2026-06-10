#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DRY_RUN=0
PLUGIN_FILTER=""
SUITE_FILTER=""
REQUIRED_GROUP=""
CLI_FILTER=""
declare -a PHPUNIT_ARGS=()
declare -a TEST_FILES=()

file_has_group() {
    local file="$1"
    local group="$2"

    grep -Eq "^[[:space:]]*\*[[:space:]]+@group[[:space:]]+${group}([[:space:]]|$)" "$file" \
        || grep -Eq "^[[:space:]]*#\[[^]]*Group\(['\"]${group}['\"]\)\]" "$file"
}

usage() {
    cat <<EOF
Usage: $(basename "$0") [--dry-run] [--plugin <name>] [--suite <Unit|FeatureAdmin|FeatureStore>] [--require-group <group>] [phpunit-args...]

Discovers and runs plugin-local tests under:
  zc_plugins/<PluginName>/<version>/tests/Unit
  zc_plugins/<PluginName>/<version>/tests/FeatureAdmin
  zc_plugins/<PluginName>/<version>/tests/FeatureStore

Examples:
  composer tests-plugin -- --dry-run
  composer tests-plugin -- --plugin gdpr-dsar
  composer tests-plugin -- --plugin gdpr-dsar --suite FeatureAdmin
  composer tests-plugin -- --plugin gdpr-dsar --require-group plugin-filesystem --group plugin-filesystem
EOF
}

extract_cli_filter() {
    for ((i = 0; i < ${#PHPUNIT_ARGS[@]}; i++)); do
        if [ "${PHPUNIT_ARGS[$i]}" = "--filter" ] && [ $((i + 1)) -lt ${#PHPUNIT_ARGS[@]} ]; then
            CLI_FILTER="${PHPUNIT_ARGS[$((i + 1))]}"
            return
        fi

        if [[ "${PHPUNIT_ARGS[$i]}" == --filter=* ]]; then
            CLI_FILTER="${PHPUNIT_ARGS[$i]#--filter=}"
            return
        fi
    done
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

matches_required_group() {
    local file="$1"

    if [ -z "$REQUIRED_GROUP" ]; then
        return 0
    fi

    file_has_group "$file" "$REQUIRED_GROUP"
}

discover_suite_files() {
    local suite="$1"
    local search_root="$ROOT_DIR/zc_plugins"

    if [ ! -d "$search_root" ]; then
        return
    fi

    while IFS= read -r file; do
        if [ -n "$PLUGIN_FILTER" ] && [[ "$file" != "$ROOT_DIR/zc_plugins/$PLUGIN_FILTER/"* ]]; then
            continue
        fi

        if ! matches_filter "$file" "$CLI_FILTER"; then
            continue
        fi

        if ! matches_required_group "$file"; then
            continue
        fi

        printf '%s\n' "$file"
    done < <(find "$search_root" -path "*/tests/${suite}/*Test.php" -type f | sort)
}

discover_files() {
    local suites=()

    if [ -n "$SUITE_FILTER" ]; then
        suites=("$SUITE_FILTER")
    else
        suites=("Unit" "FeatureStore" "FeatureAdmin")
    fi

    for suite in "${suites[@]}"; do
        discover_suite_files "$suite"
    done
}

describe_test_file() {
    local file="$1"
    local relative="${file#$ROOT_DIR/}"
    local remainder="${relative#zc_plugins/}"
    local plugin="${remainder%%/*}"
    remainder="${remainder#*/}"
    local version="${remainder%%/*}"
    remainder="${remainder#*/tests/}"
    local suite="${remainder%%/*}"
    local test_file="${file##*/}"

    printf '%s %s %s %s' "$plugin" "$version" "$suite" "$test_file"
}

load_discovered_files() {
    TEST_FILES=()

    while IFS= read -r test_file; do
        TEST_FILES+=("$test_file")
    done < <(discover_files)
}

while [ "$#" -gt 0 ]; do
    case "$1" in
        --help|-h)
            usage
            exit 0
            ;;
        --dry-run)
            DRY_RUN=1
            shift
            ;;
        --plugin)
            PLUGIN_FILTER="${2:-}"
            shift 2
            ;;
        --plugin=*)
            PLUGIN_FILTER="${1#--plugin=}"
            shift
            ;;
        --suite)
            SUITE_FILTER="${2:-}"
            shift 2
            ;;
        --suite=*)
            SUITE_FILTER="${1#--suite=}"
            shift
            ;;
        --require-group)
            REQUIRED_GROUP="${2:-}"
            shift 2
            ;;
        --require-group=*)
            REQUIRED_GROUP="${1#--require-group=}"
            shift
            ;;
        *)
            PHPUNIT_ARGS+=("$1")
            shift
            ;;
    esac
done

case "$SUITE_FILTER" in
    ""|"Unit"|"FeatureAdmin"|"FeatureStore")
        ;;
    *)
        echo "Invalid plugin test suite: $SUITE_FILTER" >&2
        exit 1
        ;;
esac

extract_cli_filter
load_discovered_files

if [ "${#TEST_FILES[@]}" -eq 0 ]; then
    echo "No plugin-local test files matched." >&2
    exit 1
fi

if [ "$DRY_RUN" -eq 1 ]; then
    echo "RUN   [plugin-local] phpunit (dry run)"
    for file in "${TEST_FILES[@]+"${TEST_FILES[@]}"}"; do
        echo "DRY   [plugin-local] $(describe_test_file "$file")"
    done
    exit 0
fi

for file in "${TEST_FILES[@]+"${TEST_FILES[@]}"}"; do
    echo "RUN   [plugin-local] $(describe_test_file "$file")"
    "$ROOT_DIR/vendor/bin/phpunit" "${PHPUNIT_ARGS[@]+"${PHPUNIT_ARGS[@]}"}" "$file"
done
