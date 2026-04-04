#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
FAIL_ON_UNTAGGED=0
SUMMARY_ONLY=0
invalid_grouping_count=0

parallel_serial_conflict_files=()
plugin_without_serial_files=()

while [ "$#" -gt 0 ]; do
    case "$1" in
        --fail-on-untagged)
            FAIL_ON_UNTAGGED=1
            ;;
        --summary-only)
            SUMMARY_ONLY=1
            ;;
        --help|-h)
            echo "Usage: $(basename "$0") [--fail-on-untagged] [--summary-only]"
            echo
            echo "Reports explicit feature-test grouping tags and heuristic shared-state risks."
            echo "Use --fail-on-untagged to return a non-zero exit code when any feature test lacks a grouping tag"
            echo "or has an invalid explicit tag combination."
            echo "Use --summary-only to print counts and suite breakdown without the full file lists."
            exit 0
            ;;
        *)
            echo "Unknown option: $1" >&2
            echo "Usage: $(basename "$0") [--fail-on-untagged] [--summary-only]" >&2
            exit 2
            ;;
    esac
    shift
done

mapfile -t TEST_FILES < <(
    find "$ROOT_DIR/not_for_release/testFramework/FeatureStore" "$ROOT_DIR/not_for_release/testFramework/FeatureAdmin" \
        -type f -name '*Test.php' | sort
)

if [ "${#TEST_FILES[@]}" -eq 0 ]; then
    echo "No feature test files were found."
    exit 0
fi

serial_count=0
plugin_fs_count=0
parallel_candidate_count=0
untagged_count=0
direct_db_write_count=0
custom_seeder_count=0
filesystem_write_count=0
store_total_count=0
store_serial_count=0
store_parallel_candidate_count=0
store_untagged_count=0
admin_total_count=0
admin_serial_count=0
admin_parallel_candidate_count=0
admin_untagged_count=0

serial_files=()
plugin_fs_files=()
parallel_candidate_files=()
untagged_files=()
direct_db_write_files=()
custom_seeder_files=()
filesystem_write_files=()

for file in "${TEST_FILES[@]}"; do
    relative="${file#$ROOT_DIR/}"
    has_serial=0
    has_plugin_fs=0
    has_parallel_candidate=0
    has_direct_db_write=0
    has_custom_seeder=0
    has_filesystem_write=0

    if grep -Eq '^[[:space:]]*\*[[:space:]]+@group[[:space:]]+serial([[:space:]]|$)' "$file"; then
        has_serial=1
        serial_count=$((serial_count + 1))
        serial_files+=("$relative")
    fi

    if grep -Eq '^[[:space:]]*\*[[:space:]]+@group[[:space:]]+plugin-filesystem([[:space:]]|$)' "$file"; then
        has_plugin_fs=1
        plugin_fs_count=$((plugin_fs_count + 1))
        plugin_fs_files+=("$relative")
    fi

    if grep -Eq '^[[:space:]]*\*[[:space:]]+@group[[:space:]]+parallel-candidate([[:space:]]|$)' "$file"; then
        has_parallel_candidate=1
        parallel_candidate_count=$((parallel_candidate_count + 1))
        parallel_candidate_files+=("$relative")
    fi

    if grep -Eq 'TestDb::(insert|update|truncate)\(' "$file"; then
        has_direct_db_write=1
        direct_db_write_count=$((direct_db_write_count + 1))
        direct_db_write_files+=("$relative")
    fi

    if grep -Eq '(self::|\$this->)runCustomSeeder\(' "$file"; then
        has_custom_seeder=1
        custom_seeder_count=$((custom_seeder_count + 1))
        custom_seeder_files+=("$relative")
    fi

    if grep -Eq 'installPluginToFilesystem\(|removePlugin\(|touch\(|file_put_contents\(|unlink\(' "$file"; then
        has_filesystem_write=1
        filesystem_write_count=$((filesystem_write_count + 1))
        filesystem_write_files+=("$relative")
    fi

    is_store_test=0
    is_admin_test=0

    if [[ "$relative" == not_for_release/testFramework/FeatureStore/* ]]; then
        is_store_test=1
        store_total_count=$((store_total_count + 1))
    fi

    if [[ "$relative" == not_for_release/testFramework/FeatureAdmin/* ]]; then
        is_admin_test=1
        admin_total_count=$((admin_total_count + 1))
    fi

    if [ "$has_serial" -eq 0 ] && [ "$has_plugin_fs" -eq 0 ] && [ "$has_parallel_candidate" -eq 0 ]; then
        untagged_count=$((untagged_count + 1))
        untagged_files+=("$relative")
    fi

    if [ "$is_store_test" -eq 1 ]; then
        if [ "$has_serial" -eq 1 ]; then
            store_serial_count=$((store_serial_count + 1))
        fi
        if [ "$has_parallel_candidate" -eq 1 ]; then
            store_parallel_candidate_count=$((store_parallel_candidate_count + 1))
        fi
        if [ "$has_serial" -eq 0 ] && [ "$has_plugin_fs" -eq 0 ] && [ "$has_parallel_candidate" -eq 0 ]; then
            store_untagged_count=$((store_untagged_count + 1))
        fi
    fi

    if [ "$is_admin_test" -eq 1 ]; then
        if [ "$has_serial" -eq 1 ]; then
            admin_serial_count=$((admin_serial_count + 1))
        fi
        if [ "$has_parallel_candidate" -eq 1 ]; then
            admin_parallel_candidate_count=$((admin_parallel_candidate_count + 1))
        fi
        if [ "$has_serial" -eq 0 ] && [ "$has_plugin_fs" -eq 0 ] && [ "$has_parallel_candidate" -eq 0 ]; then
            admin_untagged_count=$((admin_untagged_count + 1))
        fi
    fi

    if [ "$has_serial" -eq 1 ] && [ "$has_parallel_candidate" -eq 1 ]; then
        invalid_grouping_count=$((invalid_grouping_count + 1))
        parallel_serial_conflict_files+=("$relative")
    fi

    if [ "$has_plugin_fs" -eq 1 ] && [ "$has_serial" -eq 0 ]; then
        invalid_grouping_count=$((invalid_grouping_count + 1))
        plugin_without_serial_files+=("$relative")
    fi
done

echo "Feature Test Group Report"
echo
echo "Total feature test files: ${#TEST_FILES[@]}"
echo "Tagged serial: $serial_count"
echo "Tagged plugin-filesystem: $plugin_fs_count"
echo "Tagged parallel-candidate: $parallel_candidate_count"
echo "Untagged files: $untagged_count"
echo "Heuristic direct DB writers: $direct_db_write_count"
echo "Heuristic custom seeder users: $custom_seeder_count"
echo "Heuristic filesystem writers: $filesystem_write_count"
echo "Invalid explicit group combinations: $invalid_grouping_count"
echo

echo "Suite breakdown:"
echo "  Store: total=$store_total_count, serial=$store_serial_count, parallel-candidate=$store_parallel_candidate_count, untagged=$store_untagged_count"
echo "  Admin: total=$admin_total_count, serial=$admin_serial_count, parallel-candidate=$admin_parallel_candidate_count, untagged=$admin_untagged_count"
echo

if [ "$SUMMARY_ONLY" -eq 0 ]; then
    echo "Serial-tagged files:"
    if [ "${#serial_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${serial_files[@]}"
    fi
    echo

    echo "Plugin-filesystem-tagged files:"
    if [ "${#plugin_fs_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${plugin_fs_files[@]}"
    fi
    echo

    echo "Parallel-candidate-tagged files:"
    if [ "${#parallel_candidate_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${parallel_candidate_files[@]}"
    fi
    echo

    echo "Heuristic direct DB writer files:"
    if [ "${#direct_db_write_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${direct_db_write_files[@]}"
    fi
    echo

    echo "Heuristic custom seeder files:"
    if [ "${#custom_seeder_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${custom_seeder_files[@]}"
    fi
    echo

    echo "Heuristic filesystem writer files:"
    if [ "${#filesystem_write_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${filesystem_write_files[@]}"
    fi
    echo

    echo "Untagged feature test files:"
    if [ "${#untagged_files[@]}" -eq 0 ]; then
        echo "  (none)"
    else
        printf '  %s\n' "${untagged_files[@]}"
    fi

    echo
    echo "Invalid explicit group combinations:"
    if [ "$invalid_grouping_count" -eq 0 ]; then
        echo "  (none)"
    else
        if [ "${#parallel_serial_conflict_files[@]}" -gt 0 ]; then
            echo "  serial + parallel-candidate:"
            printf '    %s\n' "${parallel_serial_conflict_files[@]}"
        fi
        if [ "${#plugin_without_serial_files[@]}" -gt 0 ]; then
            echo "  plugin-filesystem without serial:"
            printf '    %s\n' "${plugin_without_serial_files[@]}"
        fi
    fi
fi

if [ "$FAIL_ON_UNTAGGED" -eq 1 ] && [ "$untagged_count" -gt 0 ]; then
    echo
    echo "Strict mode failure: $untagged_count feature test file(s) are missing an explicit grouping tag." >&2
    exit 1
fi

if [ "$FAIL_ON_UNTAGGED" -eq 1 ] && [ "$invalid_grouping_count" -gt 0 ]; then
    echo
    echo "Strict mode failure: $invalid_grouping_count feature test file(s) have invalid explicit grouping combinations." >&2
    exit 1
fi
