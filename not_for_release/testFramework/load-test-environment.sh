#!/usr/bin/env bash

source_test_framework_env_file() {
    local env_file="$1"
    local -a preserved_names=()
    local -A preserved_values=()
    local variable_name=""

    while IFS= read -r variable_name; do
        case "$variable_name" in
            ZC_TEST_*|ZC_FEATURE_*|ZC_PARALLEL_*)
                preserved_names+=("$variable_name")
                preserved_values["$variable_name"]="${!variable_name}"
                ;;
        esac
    done < <(compgen -v)

    set -a
    # shellcheck source=/dev/null
    . "$env_file"
    set +a

    for variable_name in "${preserved_names[@]}"; do
        export "$variable_name=${preserved_values[$variable_name]}"
    done
}

load_test_framework_env() {
    local root_dir="$1"
    local env_file="${ZC_TEST_ENV_FILE:-}"

    if [ -n "$env_file" ]; then
        if [ ! -r "$env_file" ]; then
            echo "Configured test environment file not found: $env_file" >&2
            return 1
        fi

        source_test_framework_env_file "$env_file"
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
}
