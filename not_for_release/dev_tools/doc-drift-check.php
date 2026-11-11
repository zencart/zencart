<?php
/**
 * doc-drift-check.php - find claims in the agent/developer docs that the code no longer backs up.
 *
 * The prose files that steer humans and AI agents (AGENTS.md, CONVENTIONS.md, CLAUDE.md,
 * README.md, copilot-instructions.md, .ai/rules/<topic>.md, .ai/skills/<name>/SKILL.md) are full of concrete claims:
 * "copy admin/includes/dist-configure.php", "run composer run-script unit-tests",
 * "tests extend Tests\Support\zcUnitTestCase", "phpunit.xml sets APP_ENV=testing".
 * Every one of those is a fact about some other file, and nothing re-reads the doc when
 * that other file changes. This script re-reads it for you.
 *
 * What it checks (mechanically; it does not judge prose):
 *   paths     every path-like token (`includes/foo.php`, `admin/includes/`, bare `manifest.php`)
 *             must exist in the working tree. Gitignored/runtime-generated paths are listed as
 *             INFO, not errors. Placeholder/example paths (`<unique_key>`, `my_page`, ...) are
 *             skipped. When a tracked path is missing, the commit that deleted it is reported.
 *   composer  `composer run-script X` / `composer X` must name a script in composer.json.
 *   symbols   `SomeClass`, `Ns\Some\Class`, `func()`, `->method()`, `::method()`, `SOME_CONSTANT`
 *             and `SOME_PREFIX_*` must exist in the tracked PHP sources (PHP builtins are skipped).
 *   sql       `INSERT INTO t (cols)` / `FROM t` inside fenced sql blocks must match a
 *             CREATE TABLE in zc_install/sql/install/mysql_zencart.sql, columns included.
 *   settings  `NAME=value` tokens (e.g. APP_ENV=testing) must appear together on one line of
 *             some config file (phpunit.xml, composer.json, *.neon, *.yml, .editorconfig).
 *   php-range "PHP 8.3-8.5" style claims are compared with composer.json's minimum and the
 *             CI matrix maximum that is not marked continue-on-error.
 *
 * Optional modes:
 *   --stale            for each doc, list referenced paths that have commits newer than the
 *                      doc's own last commit (the doc may not have been re-read since).
 *   --changed=<range>  list doc lines that mention any file touched in `git diff <range>`
 *                      (use on a PR: --changed=origin/master...HEAD).
 *   --urls             HEAD-request every http(s) URL and report failures (network).
 *
 * Output control:
 *   --strict           exit non-zero on WARN as well as ERROR.
 *   --github           also emit ::error/::warning annotations for GitHub Actions.
 *   --quiet            suppress INFO lines.
 *
 * Usage:
 *   php not_for_release/dev_tools/doc-drift-check.php [options] [doc.md ...]
 *   composer docs-check
 *
 * Skip a single doc line by appending an HTML comment containing `doc-drift:ignore`.
 * Requires PHP 8.3+, git, and no composer packages.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------------------
// Options
// ---------------------------------------------------------------------------------------

$argvCopy = $argv;
array_shift($argvCopy);
$opts = ['strict' => false, 'github' => false, 'quiet' => false, 'stale' => false, 'urls' => false, 'changed' => null];
$docs = [];
foreach ($argvCopy as $arg) {
    if ($arg === '--strict' || $arg === '--github' || $arg === '--quiet' || $arg === '--stale' || $arg === '--urls') {
        $opts[substr($arg, 2)] = true;
    } elseif (str_starts_with($arg, '--changed=')) {
        $opts['changed'] = substr($arg, 10);
    } elseif ($arg === '-h' || $arg === '--help') {
        fwrite(STDOUT, (string)preg_replace('/^.*?\/\*\*(.*?)\*\/.*$/s', '$1', file_get_contents(__FILE__)));
        exit(0);
    } else {
        $docs[] = $arg;
    }
}

$root = realpath(__DIR__ . '/../..');
if ($root === false) {
    fwrite(STDERR, "Cannot resolve repo root\n");
    exit(2);
}
chdir($root);

if ($docs === []) {
    $docs = array_values(array_filter([
        'AGENTS.md', 'CONVENTIONS.md', 'CLAUDE.md', 'README.md', 'CONTRIBUTING.md',
        '.github/copilot-instructions.md', '.ai/README.md',
        ...glob('.ai/rules/*.md') ?: [],
        ...glob('.ai/skills/*/SKILL.md') ?: [],
        ...glob('.claude/commands/*.md') ?: [],
    ], 'is_file'));
}

// ---------------------------------------------------------------------------------------
// Findings
// ---------------------------------------------------------------------------------------

/** @var list<array{level:string,file:string,line:int,check:string,msg:string}> $findings */
$findings = [];
function finding(string $level, string $file, int $line, string $check, string $msg): void
{
    global $findings;
    $findings[] = compact('level', 'file', 'line', 'check', 'msg');
}

function sh(string $cmd): string
{
    return trim((string)shell_exec($cmd . ' 2>/dev/null'));
}

// ---------------------------------------------------------------------------------------
// Build the code index once: tracked files, basenames, classes, functions, constants
// ---------------------------------------------------------------------------------------

// Tracked plus untracked-but-not-ignored, so a doc can reference files added in the same change.
$tracked = array_flip(array_filter(explode("\n", sh('git ls-files --cached --others --exclude-standard'))));
$trackedDirs = [];
$basenames = [];
$dirBasenames = [];
foreach (array_keys($tracked) as $path) {
    $basenames[basename($path)] = true;
    $d = $path;
    while (($d = dirname($d)) !== '.' && $d !== '/') {
        $trackedDirs[$d] = true;
    }
}
foreach (array_keys($trackedDirs) as $d) {
    $dirBasenames[basename($d)] = true;
}
/** Top-level tracked directories: a bare token like `includes/foo` is only treated as a path when anchored here. */
$topDirs = array_flip(array_filter(array_keys($trackedDirs), fn ($d) => !str_contains($d, '/')));
/** Composer dev dependencies (PHPUnit attributes etc.) are only resolvable when vendor/ is installed locally. */
$vendorClasses = [];
if (is_file('vendor/composer/autoload_classmap.php')) {
    foreach (array_keys((array)include 'vendor/composer/autoload_classmap.php') as $fqcn) {
        $vendorClasses[substr($fqcn, (int)strrpos($fqcn, '\\') + 1)] = true;
    }
}

$classes = [];
$functions = [];
$constants = [];      // explicitly defined
$upperTokens = [];    // every UPPER_SNAKE token seen anywhere in PHP or SQL (usage counts as existence)
foreach (array_keys($tracked) as $path) {
    if (!str_ends_with($path, '.php') && !str_ends_with($path, '.sql')) {
        continue;
    }
    $src = @file_get_contents($path);
    if ($src === false) {
        continue;
    }
    if (str_ends_with($path, '.php')) {
        if (preg_match_all('/\b(?:class|interface|trait|enum)\s+([A-Za-z_]\w*)/', $src, $m)) {
            foreach ($m[1] as $c) {
                $classes[$c] = true;
            }
        }
        if (preg_match_all('/\bfunction\s+&?\s*([A-Za-z_]\w*)\s*\(/', $src, $m)) {
            foreach ($m[1] as $f) {
                $functions[$f] = true;
            }
        }
        if (preg_match_all('/\bdefine\(\s*[\'"]([A-Z][A-Z0-9_]*)[\'"]/', $src, $m)) {
            foreach ($m[1] as $k) {
                $constants[$k] = true;
            }
        }
        if (preg_match_all('/\bconst\s+([A-Z][A-Z0-9_]*)\b/', $src, $m)) {
            foreach ($m[1] as $k) {
                $constants[$k] = true;
            }
        }
    }
    if (preg_match_all('/\b[A-Z][A-Z0-9]*_[A-Z0-9_]+\b/', $src, $m)) {
        foreach ($m[0] as $k) {
            $upperTokens[$k] = true;
        }
    }
}

$composer = json_decode((string)file_get_contents('composer.json'), true) ?: [];
$composerScripts = array_keys($composer['scripts'] ?? []);
$composerBuiltins = ['install', 'update', 'require', 'remove', 'dump-autoload', 'dumpautoload', 'validate', 'show', 'run', 'run-script', 'exec', 'outdated', 'audit'];

$sqlSchema = [];
$sqlFile = 'zc_install/sql/install/mysql_zencart.sql';
if (is_file($sqlFile)) {
    $sql = (string)file_get_contents($sqlFile);
    if (preg_match_all('/CREATE TABLE\s+`?(\w+)`?\s*\((.*?)\)\s*ENGINE/si', $sql, $m, PREG_SET_ORDER)) {
        foreach ($m as $t) {
            $cols = [];
            foreach (preg_split('/\n/', $t[2]) as $line) {
                if (preg_match('/^\s*`?([a-z_][\w]*)`?\s+\w/i', $line, $cm) && !in_array(strtoupper($cm[1]), ['PRIMARY', 'KEY', 'UNIQUE', 'INDEX', 'FULLTEXT', 'CONSTRAINT'], true)) {
                    $cols[$cm[1]] = true;
                }
            }
            $sqlSchema[$t[1]] = $cols;
        }
    }
}

$configFiles = array_values(array_filter(array_keys($tracked), fn ($p) => preg_match('#^(composer\.json|phpunit.*\.xml|\.editorconfig|.*\.neon|\.github/workflows/.*\.ya?ml|.*\.ini|\.php-cs-fixer.*)$#', $p) === 1));

// ---------------------------------------------------------------------------------------
// Token classifiers
// ---------------------------------------------------------------------------------------

const PLACEHOLDER_SEGMENT = '/(^|[\/_.\-])(my|foo|bar|baz|example|whatever|some|placeholder|xyz|myplugin|mypage)([\/_.\-]|$)|^(PAGE_NAME|TEMPLATE|PluginName|UniqueKey|unique_key|version|admin_page_name|rewards\w*|jscript_mypage\.\w+|1\.0\.0|catalog\|admin|\.\.\.)$|[<>*|{}…]/i';
const EXAMPLE_LINE = '/\b(for example|e\.g\.|such as|like this|example)\b/i';
const PROSE_STUDLY = ['StudlyCaps', 'PascalCase', 'CamelCase', 'SnakeCase', 'PhpStorm', 'GitHub', 'PayPal', 'MyISAM', 'MySQL', 'MariaDB', 'JavaScript', 'OpenSSL', 'InnoDB', 'WordPress', 'PhpUnit', 'PostgreSQL', 'TypeScript', 'MacOS', 'iPhone', 'YouTube', 'SendGrid', 'MailChimp', 'ChatGPT', 'OpenAI'];
const PROSE_FUNCS = ['getProductName', 'doSomethingNonObvious', 'zen_something', 'install', 'remove', 'keys', 'setUp', 'tearDown'];
const KEYWORDS = ['require', 'require_once', 'include', 'include_once', 'define', 'defined', 'isset', 'empty', 'unset', 'echo', 'print', 'exit', 'die', 'list', 'array', 'global', 'function', 'match', 'eval'];

function isPlaceholderPath(string $p): bool
{
    foreach (explode('/', trim($p, '/')) as $seg) {
        if ($seg !== '' && preg_match(PLACEHOLDER_SEGMENT, $seg)) {
            return true;
        }
    }
    return false;
}

function looksLikePath(string $t): bool
{
    if (preg_match('#^https?://#', $t) || str_contains($t, ' ') || str_contains($t, '\\') || str_contains($t, '..')) {
        return false; // URLs, prose, namespaces, git ranges (origin/master...HEAD)
    }
    if (str_contains($t, '/')) {
        return (bool)preg_match('#^/?[\w.\-<>*|{}…]+(/[\w.\-<>*|{}…]+)*/?$#u', $t);
    }
    return (bool)preg_match('/^\.?[\w\-]+\.(php|md|xml|json|ya?ml|sql|js|css|ini|sh|txt|neon|dist|html|htaccess|editorconfig|gitignore|env|cache)$/i', $t)
        || (bool)preg_match('/^\.(editorconfig|gitignore|htaccess|env)$/', $t);
}

/** Check a path token; returns nothing, records findings. */
function checkPath(string $doc, int $ln, string $token, bool $exampleLine): void
{
    global $tracked, $trackedDirs, $basenames, $dirBasenames, $topDirs;
    static $deletedCache = [];
    static $suffixIndex = null;
    if ($suffixIndex === null) {
        $suffixIndex = array_merge(array_keys($tracked), array_keys($trackedDirs));
    }

    if (isPlaceholderPath($token)) {
        return;
    }
    $p = ltrim($token, '/');
    $isDir = str_ends_with($p, '/');
    $p = rtrim($p, '/');
    if ($p === '') {
        return;
    }

    // Bare filename or directory name: satisfied if it exists anywhere in the tree.
    if (!str_contains($p, '/')) {
        if (isset($basenames[$p]) || is_file($p) || isset($dirBasenames[$p]) || is_dir($p)) {
            return;
        }
        // A directory pattern such as `/tmp/` only matches when git is asked about a directory, so keep the slash.
        if (sh('git check-ignore -q ' . escapeshellarg($isDir ? $p . '/' : $p) . '; echo $?') === '0') {
            finding('INFO', $doc, $ln, 'paths', "`$token` is gitignored/runtime-generated (not verifiable, ensure its generator still exists)");
            return;
        }
        finding($exampleLine ? 'INFO' : 'WARN', $doc, $ln, 'paths', "`$token` does not exist anywhere in the tree" . ($exampleLine ? ' (example line)' : ''));
        return;
    }

    if (isset($tracked[$p]) || isset($trackedDirs[$p]) || ($isDir ? is_dir($p) : file_exists($p))) {
        if (!$isDir && isset($trackedDirs[$p]) && !isset($tracked[$p]) && !is_dir($p)) {
            // fine: referenced without trailing slash but is a dir
        }
        return;
    }
    if (sh('git check-ignore -q ' . escapeshellarg($isDir ? $p . '/' : $p) . '; echo $?') === '0') {
        finding('INFO', $doc, $ln, 'paths', "`$token` is gitignored/runtime-generated (not verifiable, ensure its generator still exists)");
        return;
    }
    // Glob-ish (e.g. includes/templates/*) -> check the parent
    if (str_contains($p, '*')) {
        $parent = dirname($p);
        if (isset($trackedDirs[$parent]) || is_dir($parent)) {
            return;
        }
    }
    // Relative sub-path (plugin-relative `Installer/ScriptedInstaller.php`, `testsSundry/FooTest.php`): any tracked path ending in it.
    $suffix = '/' . $p;
    foreach ($suffixIndex as $candidate) {
        if (str_ends_with($candidate, $suffix)) {
            return;
        }
    }
    // `file/path`, `MySQL/MariaDB`: slashed prose, not a path. Only treat as a path claim when it has an
    // extension, a trailing slash, a dotted segment, or is anchored at a top-level tracked directory.
    $first = explode('/', $p)[0];
    $pathLike = $isDir || isset($topDirs[$first]) || preg_match('/\.[a-z0-9]{1,5}$/i', $p) === 1;
    if (!$pathLike) {
        return;
    }
    $deleted = $deletedCache[$p] ??= sh('git log -1 --diff-filter=D --format="%h %ad %s" --date=short -- ' . escapeshellarg($p));
    $msg = "`$token` does not exist";
    if ($deleted !== '') {
        // A file git once tracked and then deleted is a hard fact, however the sentence around it is worded.
        finding('ERROR', $doc, $ln, 'paths', $msg . " (deleted in $deleted)");
        return;
    }
    finding($exampleLine ? 'INFO' : 'ERROR', $doc, $ln, 'paths', $msg . ($exampleLine ? ' (example line)' : ''));
}

function checkClass(string $doc, int $ln, string $token): void
{
    global $classes, $basenames, $dirBasenames, $vendorClasses;
    $name = str_contains($token, '\\') ? substr($token, (int)strrpos($token, '\\') + 1) : $token;
    if ($name === '' || in_array($name, PROSE_STUDLY, true) || preg_match(PLACEHOLDER_SEGMENT, $name)) {
        return;
    }
    if (isset($classes[$name]) || isset($basenames[$name . '.php']) || isset($dirBasenames[$name]) || class_exists($name, false) || interface_exists($name, false)) {
        return;
    }
    if (isset($vendorClasses[$name])) {
        return;
    }
    if (str_contains($token, 'PHPUnit\\') || preg_match('/^(RunTestsInSeparateProcesses|RunInSeparateProcess|Test|DataProvider|Group|Depends|CoversClass|CoversFunction|Before|After)$/', $name)) {
        return; // PHPUnit attribute/class; only resolvable when vendor/ is installed
    }
    // Namespace-only references (Aura\Autoload, Zencart\Plugins\Catalog) end in a single-hump word; skip those.
    if (str_contains($token, '\\') && !preg_match('/[a-z][A-Z]/', $name)) {
        return;
    }
    finding('ERROR', $doc, $ln, 'symbols', "class/trait `$token` not found in tracked PHP sources");
}

function checkFunction(string $doc, int $ln, string $name): void
{
    global $functions;
    if (in_array($name, KEYWORDS, true) || in_array($name, PROSE_FUNCS, true) || function_exists($name) || preg_match(PLACEHOLDER_SEGMENT, $name)) {
        return;
    }
    if (isset($functions[$name])) {
        return;
    }
    finding('ERROR', $doc, $ln, 'symbols', "function/method `$name()` not found in tracked PHP sources");
}

function checkConstant(string $doc, int $ln, string $token): void
{
    global $constants, $upperTokens;
    if (!str_contains($token, '_')) {
        return; // PSR, SQL, PHP ... prose acronyms
    }
    if (str_contains($token, '*') || str_contains($token, '...') || str_ends_with($token, '_')) {
        $prefix = rtrim(preg_replace('/[*.]+.*$/', '', $token), '_');
        foreach ($upperTokens as $k => $_) {
            if (str_starts_with($k, $prefix)) {
                return;
            }
        }
        finding('WARN', $doc, $ln, 'symbols', "no constant matching `$token` found in tracked sources");
        return;
    }
    if (isset($constants[$token]) || isset($upperTokens[$token]) || defined($token)) {
        return;
    }
    finding('ERROR', $doc, $ln, 'symbols', "constant `$token` not found in tracked sources");
}

function checkComposerScript(string $doc, int $ln, string $name): void
{
    global $composerScripts, $composerBuiltins;
    if (in_array($name, $composerScripts, true) || in_array($name, $composerBuiltins, true)) {
        return;
    }
    $near = [];
    foreach ($composerScripts as $s) {
        if (levenshtein($s, $name) <= 4 || str_contains($s, $name) || str_contains($name, $s)) {
            $near[] = $s;
        }
    }
    finding('ERROR', $doc, $ln, 'composer', "composer script `$name` is not defined in composer.json" . ($near ? ' (did you mean: ' . implode(', ', $near) . ')' : ''));
}

function checkSetting(string $doc, int $ln, string $name, string $value): void
{
    global $configFiles, $constants, $upperTokens;
    foreach ($configFiles as $cf) {
        foreach (file($cf) ?: [] as $line) {
            if (str_contains($line, $name) && str_contains($line, $value)) {
                return;
            }
        }
    }
    if (isset($constants[$name]) || isset($upperTokens[$name])) {
        return; // "set DEBUG_AUTOLOAD=true in local configure.php" is an instruction about a code constant, not a claim about a config file
    }
    finding('WARN', $doc, $ln, 'settings', "`$name=$value` not found together on any line of " . count($configFiles) . ' config files');
}

function checkPhpRange(string $doc, int $ln, string $min, string $max): void
{
    global $composer;
    $req = $composer['require']['php'] ?? '';
    $reqMin = preg_match('/(\d+\.\d+)/', $req, $m) ? $m[1] : null;
    $ciMax = null;
    foreach (glob('.github/workflows/*.yml') ?: [] as $wf) {
        $y = (string)file_get_contents($wf);
        if (!preg_match('/php-version:\s*\[([^\]]+)\]/', $y, $mm)) {
            continue;
        }
        $versions = array_map(fn ($v) => trim($v, " '\""), explode(',', $mm[1]));
        $soft = preg_match_all('/continue-on-error:.*?==\s*[\'"]([\d.]+)[\'"]/', $y, $sm) ? $sm[1] : [];
        foreach (array_diff($versions, $soft) as $v) {
            if ($ciMax === null || version_compare($v, $ciMax, '>')) {
                $ciMax = $v;
            }
        }
    }
    if ($reqMin !== null && $reqMin !== $min) {
        finding('ERROR', $doc, $ln, 'php-range', "doc says PHP minimum $min but composer.json requires php $req");
    }
    if ($ciMax !== null && $ciMax !== $max) {
        finding('WARN', $doc, $ln, 'php-range', "doc says PHP maximum $max but CI's highest non-optional version is $ciMax");
    }
}

function checkSql(string $doc, int $ln, string $stmt): void
{
    global $sqlSchema;
    if ($sqlSchema === []) {
        return;
    }
    if (preg_match('/\b(?:INSERT\s+INTO|UPDATE|FROM|JOIN)\s+`?(\w+)`?(?:\s*\(([^)]*)\))?/i', $stmt, $m)) {
        $table = $m[1];
        if (!isset($sqlSchema[$table])) {
            finding('ERROR', $doc, $ln, 'sql', "table `$table` has no CREATE TABLE in mysql_zencart.sql");
            return;
        }
        if (!empty($m[2])) {
            foreach (array_map('trim', explode(',', $m[2])) as $col) {
                $col = trim($col, '`');
                if ($col !== '' && !isset($sqlSchema[$table][$col])) {
                    finding('ERROR', $doc, $ln, 'sql', "column `$col` does not exist on table `$table`");
                }
            }
        }
    }
}

// ---------------------------------------------------------------------------------------
// Walk the docs
// ---------------------------------------------------------------------------------------

$docPathRefs = []; // doc => [path => [lines]]
$urls = [];

foreach ($docs as $doc) {
    $lines = file($doc, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        finding('ERROR', $doc, 0, 'io', 'cannot read file');
        continue;
    }
    $inFence = false;
    $fenceLang = '';
    $sqlBuffer = '';
    $sqlStart = 0;

    foreach ($lines as $i => $line) {
        $ln = $i + 1;
        if (str_contains($line, 'doc-drift:ignore')) {
            continue;
        }
        if (preg_match('/^\s*```\s*(\w*)/', $line, $fm)) {
            if ($inFence && $fenceLang === 'sql' && $sqlBuffer !== '') {
                foreach (preg_split('/;\s*/', $sqlBuffer) as $stmt) {
                    if (trim($stmt) !== '') {
                        checkSql($doc, $sqlStart, $stmt);
                    }
                }
            }
            $inFence = !$inFence;
            $fenceLang = $inFence ? strtolower($fm[1]) : '';
            $sqlBuffer = '';
            $sqlStart = $ln + 1;
            continue;
        }
        if ($inFence && $fenceLang === 'sql') {
            $sqlBuffer .= ' ' . $line;
            continue;
        }

        $exampleLine = (bool)preg_match(EXAMPLE_LINE, $line);

        // URLs
        if (preg_match_all('#https?://[^\s)`>\]]+#', $line, $um)) {
            foreach ($um[0] as $u) {
                $urls[rtrim($u, '.,;')][] = "$doc:$ln";
            }
        }

        // Backticked tokens
        $tokens = [];
        if (preg_match_all('/`([^`\n]+)`/', $line, $bm)) {
            $tokens = $bm[1];
        }
        // Bare path-ish tokens in list lines inside fences ("- includes/foo.php (comment)") and in prose
        $stripped = preg_replace('/`[^`]*`/', ' ', $line);
        $stripped = preg_replace('/\[[^\]]*\]\(https?:[^)]*\)/', ' ', (string)$stripped); // markdown links: label is prose
        $stripped = preg_replace('#https?://\S+#', ' ', (string)$stripped);
        if (preg_match_all('#(?<![\w@.])(/?[\w.\-]+(?:/[\w.\-<>*|]+)+/?|[\w\-]+\.(?:php|xml|json|ya?ml|sql|neon|ini|sh))(?![\w/])#', (string)$stripped, $pm)) {
            foreach ($pm[1] as $t) {
                // avoid version-like fragments ("8.3/8.4"), domains ("zen-cart.com/forum") and un-anchored prose ("file/path")
                if (preg_match('#^\d#', $t) || preg_match('#^[\w.-]+/\d#', $t) || preg_match('#^[\w-]+\.(com|org|net|io|dev)/#i', $t)) {
                    continue;
                }
                $first = explode('/', ltrim($t, '/'))[0];
                if (str_contains($t, '/') && !isset($topDirs[$first]) && !preg_match('/\.[a-z0-9]{1,5}$/i', rtrim($t, '/'))) {
                    continue;
                }
                $tokens[] = $t;
            }
        }

        foreach (array_unique($tokens) as $t) {
            $t = trim($t);
            if ($t === '') {
                continue;
            }
            // composer scripts
            if (preg_match('/^composer\s+(?:run-script\s+|run\s+)?([a-z][\w:\-]*)/', $t, $cm)) {
                checkComposerScript($doc, $ln, $cm[1]);
                continue;
            }
            // NAME=value settings
            if (preg_match('/^([A-Z][A-Z0-9_]+)=([\w.\-]+)$/', $t, $sm)) {
                checkSetting($doc, $ln, $sm[1], $sm[2]);
                continue;
            }
            // function / method call
            if (preg_match('/^(?:\$?\w+(?:->|::))?&?([A-Za-z_]\w*)\(\)?$/', $t, $fm2) && !str_contains($t, '/')) {
                if (str_contains($t, '->') || str_contains($t, '::') || str_ends_with($t, ')')) {
                    checkFunction($doc, $ln, $fm2[1]);
                    continue;
                }
            }
            // $var->CONST or $tplSetting->KEY style: skip
            if (str_starts_with($t, '$')) {
                continue;
            }
            // namespaced or StudlyCaps class
            if (preg_match('/^\\\\?[A-Za-z_]\w*(?:\\\\[A-Za-z_<>\w]*)+$/', $t)) {
                checkClass($doc, $ln, ltrim($t, '\\'));
                continue;
            }
            if (preg_match('/^[A-Z][a-z0-9]+(?:[A-Z][a-z0-9]*)+$/', $t)) {
                checkClass($doc, $ln, $t);
                continue;
            }
            // constants
            if (preg_match('/^[A-Z][A-Z0-9_]*(?:\*|\.\.\.[A-Z0-9_]*)?$/', $t) && (str_contains($t, '_') || strlen($t) > 3)) {
                checkConstant($doc, $ln, $t);
                continue;
            }
            // paths
            if (looksLikePath($t)) {
                checkPath($doc, $ln, $t, $exampleLine);
                $clean = rtrim(ltrim($t, '/'), '/');
                if (!isPlaceholderPath($t) && (isset($tracked[$clean]) || isset($trackedDirs[$clean]))) {
                    if (!in_array($ln, $docPathRefs[$doc][$clean] ?? [], true)) {
                        $docPathRefs[$doc][$clean][] = $ln;
                    }
                }
                continue;
            }
        }

        // Prose composer commands outside backticks ("composer run-script unit-tests")
        if (preg_match_all('/\bcomposer\s+(?:run-script\s+|run\s+)([a-z][\w:\-]*)/', (string)$stripped, $cm2)) {
            foreach ($cm2[1] as $s) {
                checkComposerScript($doc, $ln, $s);
            }
        }
        // Prose NAME=value
        if (preg_match_all('/\b([A-Z][A-Z0-9_]{2,})=([\w.\-]+)/', (string)$stripped, $sm2, PREG_SET_ORDER)) {
            foreach ($sm2 as $s) {
                checkSetting($doc, $ln, $s[1], $s[2]);
            }
        }
        // PHP version range claims
        if (preg_match('/\bPHP\s+(\d\.\d)\s*[-–]\s*(\d\.\d)\b/', $line, $vm)) {
            checkPhpRange($doc, $ln, $vm[1], $vm[2]);
        }
    }
}

// ---------------------------------------------------------------------------------------
// Optional: staleness by history
// ---------------------------------------------------------------------------------------

if ($opts['stale']) {
    foreach ($docPathRefs as $doc => $refs) {
        $docSha = sh('git log -1 --format=%H -- ' . escapeshellarg($doc));
        $docDate = sh('git log -1 --format=%ad --date=short -- ' . escapeshellarg($doc));
        if ($docSha === '') {
            continue;
        }
        foreach ($refs as $path => $lns) {
            $n = (int)sh('git rev-list --count ' . escapeshellarg($docSha) . '..HEAD -- ' . escapeshellarg($path));
            if ($n > 0) {
                $last = sh('git log -1 --format="%h %ad %s" --date=short -- ' . escapeshellarg($path));
                finding('INFO', $doc, $lns[0], 'stale', "`$path` has $n commit(s) since this doc was last edited ($docDate); latest: $last");
            }
        }
    }
}

if ($opts['changed'] !== null) {
    $changed = array_filter(explode("\n", sh('git diff --name-only ' . escapeshellarg($opts['changed']))));
    foreach ($docPathRefs as $doc => $refs) {
        foreach ($refs as $path => $lns) {
            // A mention of a file matches that file; a mention of a directory matches its direct children only
            // (a deep descendant changing says nothing about a sentence that names the directory).
            $hits = array_values(array_filter($changed, fn ($cf) => $cf === $path || dirname($cf) === $path));
            if ($hits === []) {
                continue;
            }
            $shown = implode(', ', array_map(fn ($h) => "`$h`", array_slice($hits, 0, 3))) . (count($hits) > 3 ? ' +' . (count($hits) - 3) . ' more' : '');
            finding('INFO', $doc, $lns[0], 'changed', "mentions `$path`; this change touches $shown - re-read the claim" . (count($lns) > 1 ? ' (also line ' . implode(', ', array_slice($lns, 1)) . ')' : ''));
        }
    }
}

if ($opts['urls']) {
    foreach ($urls as $u => $where) {
        $code = sh('curl -s -o /dev/null -L -A "doc-drift-check" --max-time 15 -w "%{http_code}" ' . escapeshellarg($u));
        if ($code === '' || (int)$code >= 400) {
            [$f, $l] = explode(':', $where[0]);
            finding('WARN', $f, (int)$l, 'urls', "$u returned HTTP " . ($code ?: 'no response'));
        }
    }
}

// ---------------------------------------------------------------------------------------
// Report
// ---------------------------------------------------------------------------------------

usort($findings, fn ($a, $b) => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);
$deduped = [];
foreach ($findings as $f) {
    $key = $f['file'] . "\0" . $f['level'] . "\0" . $f['msg'];
    if (isset($deduped[$key])) {
        $deduped[$key]['also'][] = $f['line'];
        continue;
    }
    $f['also'] = [];
    $deduped[$key] = $f;
}
$findings = array_values($deduped);
usort($findings, fn ($a, $b) => [$a['file'], $a['line']] <=> [$b['file'], $b['line']]);
$counts = ['ERROR' => 0, 'WARN' => 0, 'INFO' => 0];
$current = null;
foreach ($findings as $f) {
    $counts[$f['level']]++;
    if ($f['level'] === 'INFO' && $opts['quiet']) {
        continue;
    }
    if ($current !== $f['file']) {
        $current = $f['file'];
        echo "\n== {$f['file']}\n";
    }
    printf("  %s:%d [%s/%s] %s%s\n", $f['file'], $f['line'], $f['level'], $f['check'], $f['msg'], $f['also'] ? ' (also line ' . implode(', ', $f['also']) . ')' : '');
    if ($opts['github'] && $f['level'] !== 'INFO') {
        $kind = $f['level'] === 'ERROR' ? 'error' : 'warning';
        $msg = str_replace(["\r", "\n"], ' ', $f['msg']);
        printf("::%s file=%s,line=%d,title=doc-drift/%s::%s\n", $kind, $f['file'], $f['line'], $f['check'], $msg);
    }
}

printf("\n%d doc file(s) checked: %d error(s), %d warning(s), %d info.\n", count($docs), $counts['ERROR'], $counts['WARN'], $counts['INFO']);
$fail = $counts['ERROR'] > 0 || ($opts['strict'] && $counts['WARN'] > 0);
exit($fail ? 1 : 0);
