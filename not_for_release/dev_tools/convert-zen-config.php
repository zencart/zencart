<?php
/**
 * Mechanically convert bare CONFIG_KEY constant references to zen_config('CONFIG_KEY')
 * calls, for keys listed in the repo-root config_keys.php master list.
 *
 * This consolidates three one-off scripts used during the zen_config migration
 * (audit_config_keys.php, convert_config_keys.php, find_risky_zen_config.php) into a
 * single tool, plus two checks that earlier passes got wrong by hand:
 *
 *   1. Keys in $non_db_settings (includes/init_includes/init_non_db_settings.php) are
 *      resolved via zen_define_default(), NOT the TABLE_CONFIGURATION repository - they
 *      must never become zen_config() calls. (TOPMOST_CATEGORY_PARENT_ID was wrongly
 *      converted this way in an earlier manual pass and had to be reverted everywhere.)
 *   2. A bare constant used as a parameter default, a promoted-constructor-property
 *      default, or a class property default sits in a "constant expression" context -
 *      PHP does not allow a function call there. zen_config() cannot be dropped in
 *      directly; the signature needs `= null` (or a sentinel) plus an in-body resolve.
 *      This script does NOT attempt that rewrite - it flags the site for a human to fix,
 *      since picking the right sentinel and resolving it correctly requires judgment.
 *
 * Every occurrence found is printed - one line per hit, in dry-run as well as --apply
 * mode - so you get a full per-file/per-line record of what happened, not just the
 * aggregate counts. Per occurrence, this script decides one of four things:
 *   - CONVERTED:        safe context -> rewritten to zen_config('KEY')
 *   - CONVERTED (flag): same rewrite, but the call is passed directly as an argument to
 *                        another function with no default supplied - flagged for a
 *                        null-safety read, same heuristic as find_risky_zen_config.php
 *   - FLAGGED (default): left untouched - sits in a parameter/property default position;
 *                        needs a manual `= null` + in-body resolve, then re-run
 *   - SKIPPED (non-db):  left untouched - key belongs to $non_db_settings, never convert
 *
 * Usage:
 *   php convert-zen-config.php <target-dir> [--apply] [--key=SOME_KEY]
 *
 *   <target-dir>   Directory to scan recursively for .php files (admin, includes,
 *                   zc_plugins, or a subdirectory of any of those).
 *   --apply        Actually write changes. Without this flag, dry-run report only.
 *   --key=KEY      Restrict to a single key (handy when chasing one conversion down).
 *
 * Run this on a clean git working tree, so you can review/diff/discard with git
 * afterward. There is no separate revert mode - use `git diff` / `git checkout -- <file>`.
 */

declare(strict_types=1);

// This is a dev-only CLI tool that recursively walks a directory and, with --apply,
// overwrites every .php file it finds a hit in. It must never run under a web SAPI:
// if this file were ever reachable over HTTP (e.g. accidentally deployed, or via the
// old php-cgi query-string-as-argv misconfiguration), that combination becomes an
// arbitrary-file-write primitive. Refuse outright unless invoked from the CLI.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script is a CLI-only developer tool and cannot be run via a web server.');
}

function main(array $argv): int
{
    $repoRoot = realpath(dirname(__DIR__, 2));

    $targetDir = null;
    $apply = false;
    $onlyKey = null;
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--apply') {
            $apply = true;
        } elseif (str_starts_with($arg, '--key=')) {
            $onlyKey = substr($arg, strlen('--key='));
        } elseif ($targetDir === null) {
            $targetDir = $arg;
        }
    }

    if ($targetDir === null || !is_dir($targetDir)) {
        fwrite(STDERR, "Usage: php convert-zen-config.php <target-dir> [--apply] [--key=SOME_KEY]\n(<target-dir> would be admin, includes, zc_plugins, or a subdirectory.)\n(Use --apply to write the fixes. Default mode is reporting only.)\nSee source code for more details.\n");
        return 1;
    }

    // Containment check: refuse to touch anything outside the repo, even if a hostile
    // or mistaken path was passed - this is the thing that turns "wrote 30 files" into
    // "wrote 30 files in /etc" if --apply is ever combined with a bad target-dir.
    $resolvedTarget = realpath($targetDir);
    if ($resolvedTarget === false || !str_starts_with($resolvedTarget . DIRECTORY_SEPARATOR, $repoRoot . DIRECTORY_SEPARATOR)) {
        fwrite(STDERR, "Refusing to run: <target-dir> must resolve to a path inside the repo ($repoRoot).\n");
        return 1;
    }

    $keysFile = $repoRoot . '/config_keys.php';
    if (!is_file($keysFile)) {
        fwrite(STDERR, "Cannot find config_keys.php at $keysFile\n");
        return 1;
    }
    require $keysFile; // defines $keys
    $masterKeySet = array_fill_keys($keys, true);

    $nonDbKeys = extractNonDbSettingsKeys($repoRoot . '/includes/init_includes/init_non_db_settings.php');
    $nonDbKeySet = array_fill_keys($nonDbKeys, true);

    if ($onlyKey !== null) {
        $masterKeySet = isset($masterKeySet[$onlyKey]) ? [$onlyKey => true] : [];
    }

    $files = findPhpFiles($targetDir);
    sort($files);

    $totals = ['converted' => 0, 'convertedFlagged' => 0, 'flaggedDefault' => 0, 'skippedNonDb' => 0];
    $filesChanged = 0;
    $report = [];

    foreach ($files as $path) {
        if (realpath($path) === realpath($keysFile)) {
            continue;
        }

        $result = processFile($path, $masterKeySet, $nonDbKeySet);

        foreach (['converted', 'convertedFlagged', 'flaggedDefault', 'skippedNonDb'] as $k) {
            $totals[$k] += $result[$k];
        }

        if ($result['converted'] > 0 || $result['convertedFlagged'] > 0) {
            $filesChanged++;
            if ($apply) {
                file_put_contents($path, $result['output']);
            }
        }

        foreach ($result['notes'] as $note) {
            $note['file'] = $path;
            $report[] = $note;
        }
    }

    foreach ($report as $note) {
        echo "[{$note['tag']}] {$note['file']}:{$note['line']}\t{$note['key']}";
        if ($note['detail'] !== '') {
            echo "\t{$note['detail']}";
        }
        echo "\n";
    }

    $totalConverted = $totals['converted'] + $totals['convertedFlagged'];

    echo "\n--- Summary ---\n";
    echo "Files changed: $filesChanged\n";
    echo "Converted: $totalConverted ({$totals['convertedFlagged']} flagged for null-safety review)\n";
    echo "Flagged - parameter/property default, needs manual rewrite: {$totals['flaggedDefault']}\n";
    echo "Skipped - non-DB setting (zen_define_default), left untouched: {$totals['skippedNonDb']}\n";

    if (!$apply && $totalConverted > 0) {
        echo "\nThis was a dry run - no files were modified. Re-run with --apply to write changes.\n";
    }

    return 0;
}

/**
 * @return list<string>
 */
function findPhpFiles(string $targetDir): array
{
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($targetDir, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

/**
 * Extracts the 'KEY' => ... entries from $non_db_settings in
 * init_non_db_settings.php, without executing the file (it references constants
 * and calls zen_define_default() that aren't safe to run outside bootstrap).
 *
 * @return list<string>
 */
function extractNonDbSettingsKeys(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $src = file_get_contents($path);

    // Slice out just the $non_db_settings = [ ... ]; array literal, then pull
    // quoted ALL_CAPS keys followed by '=>' out of that slice.
    if (!preg_match('/\$non_db_settings\s*=\s*\[(.*?)\];/s', $src, $m)) {
        return [];
    }

    preg_match_all("/'([A-Z][A-Z0-9_]+)'\\s*=>/", $m[1], $keyMatches);

    return $keyMatches[1];
}

/**
 * @param array<string, true> $masterKeySet
 * @param array<string, true> $nonDbKeySet
 * @return array{
 *     output: string, converted: int, convertedFlagged: int, flaggedDefault: int,
 *     skippedNonDb: int, notes: list<array{line:int, key:string, tag:string, detail:string}>
 * }
 */
function processFile(string $path, array $masterKeySet, array $nonDbKeySet): array
{
    $src = file_get_contents($path);
    $tokens = token_get_all($src);
    $count = count($tokens);

    $defaultContext = buildDefaultContextMap($tokens);

    $out = '';
    $converted = 0;
    $convertedFlagged = 0;
    $flaggedDefault = 0;
    $skippedNonDb = 0;
    $notes = [];

    for ($i = 0; $i < $count; $i++) {
        $tok = $tokens[$i];

        if (!is_array($tok)) {
            $out .= $tok;
            continue;
        }

        [$id, $text, $line] = $tok;

        if ($id !== T_STRING || !isset($masterKeySet[$text])) {
            $out .= $text;
            continue;
        }

        $prev = previousSignificantToken($tokens, $i);
        $prev2 = $prev !== null ? previousSignificantToken($tokens, $prev['index']) : null;
        $next = nextSignificantToken($tokens, $i, $count);

        // Skip: preceded by -> or :: (object/class member access)
        if ($prev && in_array($prev['text'], ['->', '::'], true)) {
            $out .= $text;
            continue;
        }
        // Skip: followed by ( -> this identifier is being called as a function, not
        // referenced as a constant (e.g. a user-defined function that happens to share
        // a name with a config key - vanishingly rare, but cheap to guard against).
        if ($next && $next['text'] === '(') {
            $out .= $text;
            continue;
        }
        // Skip: defined()/!defined() guard argument
        if ($prev && strtolower($prev['text']) === 'defined') {
            $out .= $text;
            continue;
        }
        // Skip: class constant declaration (const FOO = SOME_KEY;) - compile-time
        // constant expression, same restriction as below but via a different keyword.
        if ($prev2 && strtolower($prev2['text']) === 'const') {
            $out .= $text;
            continue;
        }

        // Non-DB setting: never convert, just flag for visibility.
        if (isset($nonDbKeySet[$text])) {
            $out .= $text;
            $skippedNonDb++;
            $notes[] = ['line' => $line, 'key' => $text, 'tag' => 'SKIPPED-non-db', 'detail' => 'resolved via zen_define_default() in init_non_db_settings.php - never convert'];
            continue;
        }

        // Parameter/property default: PHP forbids a function call in a constant
        // expression here. Leave untouched; needs a manual `= null` + in-body resolve.
        if (!empty($defaultContext[$i])) {
            $out .= $text;
            $flaggedDefault++;
            $notes[] = ['line' => $line, 'key' => $text, 'tag' => 'FLAGGED-default-context', 'detail' => 'parameter/property default - change to `= null` and resolve zen_config() in the body, then re-run'];
            continue;
        }

        $out .= "zen_config('$text')";

        // Null-safety check: is this call (now with no default) passed directly as an
        // argument to some other function? Same heuristic as find_risky_zen_config.php.
        $riskyCallee = riskyArgumentContext($prev, $tokens, $i);
        if ($riskyCallee !== null) {
            $convertedFlagged++;
            $notes[] = ['line' => $line, 'key' => $text, 'tag' => 'CONVERTED-flag', 'detail' => "passed directly into $riskyCallee(...) with no default - verify it tolerates null, or add a default"];
        } else {
            $converted++;
            $notes[] = ['line' => $line, 'key' => $text, 'tag' => 'CONVERTED', 'detail' => ''];
        }
    }

    return [
        'output' => $out,
        'converted' => $converted,
        'convertedFlagged' => $convertedFlagged,
        'flaggedDefault' => $flaggedDefault,
        'skippedNonDb' => $skippedNonDb,
        'notes' => $notes,
    ];
}

/**
 * Returns a [token_index => bool] map: true if that token sits inside a parameter
 * default, promoted-constructor-property default, or class property default - i.e.
 * a PHP "constant expression" context where a function call isn't allowed.
 *
 * @param list<mixed> $tokens
 * @return array<int, bool>
 */
function buildDefaultContextMap(array $tokens): array
{
    $count = count($tokens);
    $flag = [];

    $braceDepth = 0;
    $parenDepth = 0;
    $bracketDepth = 0;

    $classBodyStack = []; // brace depths at which a class/trait body began
    $pendingClassKeyword = false;

    $sigStack = []; // frames: ['baseParen'=>int, 'baseBracket'=>int, 'afterEquals'=>bool]
    $pendingFuncKeyword = false;

    $propDefaultActive = false;

    for ($i = 0; $i < $count; $i++) {
        $tok = $tokens[$i];
        $id = is_array($tok) ? $tok[0] : null;
        $text = is_array($tok) ? $tok[1] : $tok;

        if ($id === T_CLASS || $id === T_TRAIT) {
            $pendingClassKeyword = true;
        }
        if ($id === T_FUNCTION || $id === T_FN) {
            $pendingFuncKeyword = true;
        }

        if ($text === '(') {
            if ($pendingFuncKeyword) {
                $sigStack[] = ['baseParen' => $parenDepth, 'baseBracket' => $bracketDepth, 'afterEquals' => false];
                $pendingFuncKeyword = false;
            }
            $parenDepth++;
        } elseif ($text === ')') {
            $parenDepth--;
            if (!empty($sigStack) && end($sigStack)['baseParen'] === $parenDepth) {
                array_pop($sigStack);
            }
        } elseif ($text === '[') {
            $bracketDepth++;
        } elseif ($text === ']') {
            $bracketDepth--;
        } elseif ($text === '{') {
            $braceDepth++;
            if ($pendingClassKeyword) {
                $classBodyStack[] = $braceDepth;
                $pendingClassKeyword = false;
            }
        } elseif ($text === '}') {
            if (!empty($classBodyStack) && end($classBodyStack) === $braceDepth) {
                array_pop($classBodyStack);
            }
            $braceDepth--;
            $propDefaultActive = false;
        }

        // Parameter/promoted-property default: while inside a function signature's
        // outer parens, and we've seen a top-level '=' since the last top-level ',',
        // everything up to the next top-level ',' or the signature's ')' is flagged.
        if (!empty($sigStack)) {
            $topIdx = count($sigStack) - 1;
            $top = $sigStack[$topIdx];
            $atSigTopLevel = $parenDepth === $top['baseParen'] + 1 && $bracketDepth === $top['baseBracket'];
            if ($atSigTopLevel && $text === '=') {
                $sigStack[$topIdx]['afterEquals'] = true;
            } elseif ($atSigTopLevel && $text === ',') {
                $sigStack[$topIdx]['afterEquals'] = false;
            }
            if ($sigStack[$topIdx]['afterEquals']) {
                $flag[$i] = true;
            }
        }

        // Class property default: directly inside a class body (not inside any method
        // body or signature - guaranteed by parenDepth/bracketDepth both being zero),
        // from an '=' through to the terminating ';'.
        $inClassBodyTop = !empty($classBodyStack) && end($classBodyStack) === $braceDepth;
        if ($inClassBodyTop && $parenDepth === 0 && $bracketDepth === 0) {
            if ($text === '=' && !$propDefaultActive) {
                $propDefaultActive = true;
            } elseif ($text === ';') {
                $propDefaultActive = false;
            }
            if ($propDefaultActive) {
                $flag[$i] = true;
            }
        } else {
            $propDefaultActive = false;
        }
    }

    return $flag;
}

/**
 * If the zen_config('KEY') call just emitted (ending at token index $i, the closing
 * quote of the key string... actually called right after we've decided to convert,
 * so $i is still the original bare-constant T_STRING index) sits directly as an
 * argument to another function call with no default supplied, returns that function's
 * name. Otherwise null. Mirrors find_risky_zen_config.php's heuristic.
 *
 * @return array{index:int,text:string}|null $prev (already computed by caller)
 */
function riskyArgumentContext(?array $prev, array $tokens, int $i): ?string
{
    if ($prev === null || !in_array($prev['text'], ['(', ','], true)) {
        return null;
    }
    if ($prev['id'] === T_INT_CAST) {
        return null;
    }

    $parenIdx = null;
    if ($prev['text'] === '(') {
        $parenIdx = $prev['index'];
    } else {
        $depth = 0;
        for ($m = $i - 1; $m >= 0; $m--) {
            $val = is_array($tokens[$m]) ? $tokens[$m][1] : $tokens[$m];
            if ($val === ')') {
                $depth++;
            } elseif ($val === '(') {
                if ($depth === 0) {
                    $parenIdx = $m;
                    break;
                }
                $depth--;
            }
        }
    }
    if ($parenIdx === null) {
        return null;
    }

    $fn = null;
    for ($m = $parenIdx - 1; $m >= 0; $m--) {
        $t = $tokens[$m];
        $tid = is_array($t) ? $t[0] : null;
        if ($tid === T_WHITESPACE || $tid === T_COMMENT || $tid === T_DOC_COMMENT) {
            continue;
        }
        if ($tid === T_STRING) {
            $fn = $t[1];
        }
        break;
    }

    if ($fn === null || $fn === 'zen_config') {
        return null;
    }

    return $fn;
}

/**
 * @return array{index:int,id:int|null,text:string}|null
 */
function previousSignificantToken(array $tokens, int $i): ?array
{
    for ($j = $i - 1; $j >= 0; $j--) {
        $t = $tokens[$j];
        $tid = is_array($t) ? $t[0] : null;
        if ($tid === T_WHITESPACE || $tid === T_COMMENT || $tid === T_DOC_COMMENT) {
            continue;
        }
        return ['index' => $j, 'id' => $tid, 'text' => is_array($t) ? $t[1] : $t];
    }
    return null;
}

/**
 * @return array{index:int,id:int|null,text:string}|null
 */
function nextSignificantToken(array $tokens, int $i, int $count): ?array
{
    for ($j = $i + 1; $j < $count; $j++) {
        $t = $tokens[$j];
        $tid = is_array($t) ? $t[0] : null;
        if ($tid === T_WHITESPACE || $tid === T_COMMENT || $tid === T_DOC_COMMENT) {
            continue;
        }
        return ['index' => $j, 'id' => $tid, 'text' => is_array($t) ? $t[1] : $t];
    }
    return null;
}

exit(main($argv));
