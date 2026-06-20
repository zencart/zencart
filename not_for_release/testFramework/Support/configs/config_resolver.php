<?php

if (!function_exists('zc_test_framework_normalize_branch_family')) {
    function zc_test_framework_normalize_branch_family(?string $branchFamily): ?string
    {
        if ($branchFamily === null) {
            return null;
        }

        $branchFamily = trim($branchFamily);
        if ($branchFamily === '') {
            return null;
        }

        return str_replace(['/', '\\', ' '], '-', $branchFamily);
    }
}

if (!function_exists('zc_test_framework_git_command')) {
    function zc_test_framework_git_command(string $repositoryRoot, string $command): ?string
    {
        $repositoryRoot = realpath($repositoryRoot) ?: $repositoryRoot;
        $output = [];
        $exitCode = 0;
        exec('cd ' . escapeshellarg($repositoryRoot) . ' && ' . $command . ' 2>/dev/null', $output, $exitCode);

        if ($exitCode !== 0) {
            return null;
        }

        $result = trim(implode("\n", $output));
        return $result === '' ? null : $result;
    }
}

if (!function_exists('zc_test_framework_branch_candidates')) {
    function zc_test_framework_branch_candidates(): array
    {
        $configured = getenv('ZENCART_TESTFRAMEWORK_BRANCH_CANDIDATES') ?: 'master,2.2';
        $branches = array_filter(array_map('trim', explode(',', $configured)));

        return array_values(array_unique($branches));
    }
}

if (!function_exists('zc_test_framework_detect_branch_family')) {
    function zc_test_framework_detect_branch_family(?string $repositoryRoot = null): ?string
    {
        static $cache = [];

        $repositoryRoot ??= dirname(__DIR__, 4);
        $cacheKey = $repositoryRoot;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        foreach (['ZENCART_TESTFRAMEWORK_BRANCH_FAMILY', 'ZENCART_TESTFRAMEWORK_BASE_BRANCH', 'GITHUB_BASE_REF'] as $variable) {
            $value = zc_test_framework_normalize_branch_family(getenv($variable) ?: null);
            if ($value !== null) {
                return $cache[$cacheKey] = $value;
            }
        }

        $currentBranch = zc_test_framework_normalize_branch_family(
            zc_test_framework_git_command($repositoryRoot, 'git branch --show-current')
        );

        if ($currentBranch !== null && in_array($currentBranch, zc_test_framework_branch_candidates(), true)) {
            return $cache[$cacheKey] = $currentBranch;
        }

        $bestMatch = null;
        $bestDistance = null;
        foreach (zc_test_framework_branch_candidates() as $branchCandidate) {
            $refs = ['origin/' . $branchCandidate, $branchCandidate];

            foreach ($refs as $ref) {
                $mergeBase = zc_test_framework_git_command(
                    $repositoryRoot,
                    'git merge-base HEAD ' . escapeshellarg($ref)
                );

                if ($mergeBase === null) {
                    continue;
                }

                $distance = zc_test_framework_git_command(
                    $repositoryRoot,
                    'git rev-list --count ' . escapeshellarg($mergeBase . '..HEAD')
                );

                if ($distance === null || !ctype_digit($distance)) {
                    continue;
                }

                $distance = (int) $distance;
                if ($bestDistance === null || $distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestMatch = $branchCandidate;
                }

                break;
            }
        }

        return $cache[$cacheKey] = zc_test_framework_normalize_branch_family($bestMatch);
    }
}

if (!function_exists('zc_test_framework_config_candidates')) {
    function zc_test_framework_config_candidates(string $basePath, array $users, string $context, ?string $branchFamily = null): array
    {
        $candidates = [];
        foreach (array_values(array_unique($users)) as $user) {
            if ($branchFamily !== null) {
                $candidates[] = $basePath . $user . '.' . $branchFamily . '.' . $context . '.configure.php';
            }

            $candidates[] = $basePath . $user . '.' . $context . '.configure.php';
        }

        return $candidates;
    }
}

if (!function_exists('zc_test_framework_resolve_config_file')) {
    function zc_test_framework_resolve_config_file(string $basePath, array $users, string $context, ?string $repositoryRoot = null): ?string
    {
        $branchFamily = zc_test_framework_detect_branch_family($repositoryRoot);

        foreach (zc_test_framework_config_candidates($basePath, $users, $context, $branchFamily) as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
