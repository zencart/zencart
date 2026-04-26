#!/usr/bin/env bash

source_test_framework_env_file() {
    local env_file="$1"

    set -a
    # shellcheck source=/dev/null
    . "$env_file"
    set +a
}

apply_profile_db_defaults() {
    local root_dir="$1"
    local resolver="$root_dir/not_for_release/testFramework/resolve-profile-db-config.php"
    local -a resolved=()

    if [ ! -f "$resolver" ] || ! command -v php >/dev/null 2>&1; then
        return 0
    fi

    mapfile -t resolved < <(php "$resolver" "$root_dir")
    if [ "${#resolved[@]}" -lt 5 ]; then
        return 0
    fi

    if [ -z "${ZC_TEST_DB_HOST+x}" ] && [ -n "${resolved[0]}" ]; then
        export ZC_TEST_DB_HOST="${resolved[0]}"
    fi

    if [ -z "${ZC_TEST_DB_PORT+x}" ] && [ -n "${resolved[1]}" ]; then
        export ZC_TEST_DB_PORT="${resolved[1]}"
    fi

    if [ -z "${ZC_TEST_DB_USER+x}" ] && [ -n "${resolved[2]}" ]; then
        export ZC_TEST_DB_USER="${resolved[2]}"
    fi

    if [ -z "${ZC_TEST_DB_PASSWORD+x}" ]; then
        export ZC_TEST_DB_PASSWORD="${resolved[3]}"
    fi

    if [ -z "${ZC_TEST_DB_BASE_NAME+x}" ] && [ -n "${resolved[4]}" ]; then
        export ZC_TEST_DB_BASE_NAME="${resolved[4]}"
    fi
}

load_test_framework_env() {
    local root_dir="$1"
    local env_file="${ZC_TEST_ENV_FILE:-}"
    local -a preserved_assignments=()
    local variable_name=""
    local variable_value=""

    while IFS= read -r variable_name; do
        case "$variable_name" in
            ZC_TEST_*|ZC_FEATURE_*|ZC_PARALLEL_*)
                variable_value="${!variable_name}"
                preserved_assignments+=("$variable_name=$variable_value")
                ;;
        esac
    done < <(compgen -v)

    if [ -n "$env_file" ]; then
        if [ ! -r "$env_file" ]; then
            echo "Configured test environment file not found: $env_file" >&2
            return 1
        fi

        source_test_framework_env_file "$env_file"
        if [ "${#preserved_assignments[@]}" -gt 0 ]; then
            for variable_name in "${preserved_assignments[@]}"; do
                export "$variable_name"
            done
        fi
        apply_profile_db_defaults "$root_dir"
        return 0
    fi

    local canonical_default_env="$root_dir/not_for_release/testFramework/Support/configs/test-runner.env"
    local canonical_local_env="$root_dir/not_for_release/testFramework/Support/configs/test-runner.local.env"
    local legacy_default_env="$root_dir/not_for_release/testFramework/test-framework.env"
    local legacy_local_env="$root_dir/not_for_release/testFramework/test-framework.local.env"

    for env_file in "$canonical_default_env" "$canonical_local_env" "$legacy_default_env" "$legacy_local_env"; do
        if [ ! -f "$env_file" ]; then
            continue
        fi

        source_test_framework_env_file "$env_file"
    done

    if [ "${#preserved_assignments[@]}" -gt 0 ]; then
        for variable_name in "${preserved_assignments[@]}"; do
            export "$variable_name"
        done
    fi

    apply_profile_db_defaults "$root_dir"
}
