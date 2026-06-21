<?php
/**
 * Converts zen_config('KEY'[, $default]) calls and bare CONSTANT_NAME references to
 * $tplSetting->KEY, for a fixed allowlist of known-safe template-display settings.
 *
 * Only the keys in TPLSETTING_KEYS below are ever touched. Anything else - regardless of
 * how plausible it looks - is left alone. This is deliberate: deciding whether a given
 * constant is a safe candidate for $tplSetting (vs. core/site-wide/security/business-logic
 * config that must stay zen_config()) requires manual review; this script only mechanizes
 * the conversion once that review has already happened and a key has been added to the list.
 *
 * Scope rule: a converted file must guarantee $tplSetting is already a live global in its
 * scope. That's true for any procedural code that runs as part of catalog bootstrap (which
 * is what this script assumes you're pointing it at - template files, page/sidebox module
 * files, etc.). It is NOT initialized in Admin context. This script does not attempt to
 * verify catalog-vs-admin reachability for you - point it only at catalog-side code.
 *
 * Per occurrence found, this script decides one of three things:
 *   - CONVERTED:    top-level/procedural scope -> rewritten to $tplSetting->KEY
 *                   (a zen_config() default argument becomes ($tplSetting->KEY ?? $default)
 *                   rather than being dropped, to preserve null-safety; flagged for review
 *                   regardless, since the default may now be removable)
 *   - FLAGGED (function/method scope): left untouched. $tplSetting needs an explicit
 *                   `global $tplSetting;` in that scope before it's safe to convert -
 *                   add it by hand, then re-run this script (already-converted lines are
 *                   left alone, so it's safe to run repeatedly).
 *
 * Usage:
 *   php convert-tplsettings.php <target-dir> [--apply]
 *
 *   <target-dir>   Directory to scan recursively for .php files (e.g. a template's own
 *                   directory, includes/modules, or a plugin's catalog/includes tree).
 *   --apply        Actually write changes. Without this flag, the script only prints a
 *                   dry-run report - nothing on disk is modified.
 *
 * Run this on a clean git working tree, so you can review/diff/discard with git afterward.
 * There is no separate revert mode - use `git diff` / `git checkout -- <file>` to undo.
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

const TPLSETTING_KEYS = [
    'BEST_SELLERS_TRUNCATE',
    'BEST_SELLERS_TRUNCATE_MORE',
    'BOX_WIDTH_LEFT',
    'BOX_WIDTH_RIGHT',
    'BREAD_CRUMBS_SEPARATOR',
    'CATEGORIES_COUNT_PREFIX',
    'CATEGORIES_COUNT_SUFFIX',
    'CATEGORIES_COUNT_ZERO',
    'CATEGORIES_SEPARATOR',
    'CATEGORIES_TABS_STATUS',
    'CATEGORY_ICON_IMAGE_HEIGHT',
    'CATEGORY_ICON_IMAGE_WIDTH',
    'COLUMN_LEFT_STATUS',
    'COLUMN_RIGHT_STATUS',
    'COLUMN_WIDTH_LEFT',
    'COLUMN_WIDTH_RIGHT',
    'CUSTOMERS_AUTHORIZATION_COLUMN_LEFT_OFF',
    'CUSTOMERS_AUTHORIZATION_COLUMN_RIGHT_OFF',
    'CUSTOMERS_AUTHORIZATION_FOOTER_OFF',
    'CUSTOMERS_AUTHORIZATION_HEADER_OFF',
    'DEFINE_BREADCRUMB_STATUS',
    'DEFINE_CHECKOUT_SUCCESS_STATUS',
    'DEFINE_CONDITIONS_STATUS',
    'DEFINE_CONTACT_US_STATUS',
    'DEFINE_DISCOUNT_COUPON_STATUS',
    'DEFINE_MAIN_PAGE_STATUS',
    'DEFINE_PAGE_2_STATUS',
    'DEFINE_PAGE_3_STATUS',
    'DEFINE_PAGE_4_STATUS',
    'DEFINE_PRIVACY_STATUS',
    'DEFINE_SHIPPINGINFO_STATUS',
    'DEFINE_SITE_MAP_STATUS',
    'DOWN_FOR_MAINTENANCE_COLUMN_LEFT_OFF',
    'DOWN_FOR_MAINTENANCE_COLUMN_RIGHT_OFF',
    'DOWN_FOR_MAINTENANCE_FOOTER_OFF',
    'DOWN_FOR_MAINTENANCE_HEADER_OFF',
    'EZPAGES_SEPARATOR_FOOTER',
    'EZPAGES_SEPARATOR_HEADER',
    'EZPAGES_SHOW_PREV_NEXT_BUTTONS',
    'EZPAGES_SHOW_TABLE_CONTENTS',
    'EZPAGES_STATUS_FOOTER',
    'EZPAGES_STATUS_HEADER',
    'EZPAGES_STATUS_SIDEBOX',
    'IMAGE_PRODUCT_LISTING_HEIGHT',
    'IMAGE_PRODUCT_LISTING_WIDTH',
    'IMAGE_SHOPPING_CART_HEIGHT',
    'IMAGE_SHOPPING_CART_STATUS',
    'IMAGE_SHOPPING_CART_WIDTH',
    'IMAGE_USE_CSS_BUTTONS',
    'MAX_DISPLAY_ALSO_PURCHASED',
    'MAX_DISPLAY_BESTSELLERS',
    'MAX_DISPLAY_MANUFACTURER_NAME_LEN',
    'MAX_DISPLAY_MUSIC_GENRES_NAME_LEN',
    'MAX_DISPLAY_NEW_REVIEWS',
    'MAX_DISPLAY_ORDER_HISTORY',
    'MAX_DISPLAY_PAGE_LINKS',
    'MAX_DISPLAY_PAGE_LINKS_MOBILE',
    'MAX_DISPLAY_PRODUCTS_IN_ORDER_HISTORY_BOX',
    'MAX_DISPLAY_PRODUCTS_LISTING',
    'MAX_DISPLAY_RECORD_COMPANY_NAME_LEN',
    'MAX_LANGUAGE_FLAGS_COLUMNS',
    'MAX_MANUFACTURERS_LIST',
    'MAX_PREVIEW',
    'MAX_RANDOM_SELECT_FEATURED_PRODUCTS',
    'MAX_RANDOM_SELECT_NEW',
    'MAX_RANDOM_SELECT_REVIEWS',
    'MAX_RANDOM_SELECT_SPECIALS',
    'MEDIUM_IMAGE_HEIGHT',
    'MEDIUM_IMAGE_WIDTH',
    'MIN_DISPLAY_ALSO_PURCHASED',
    'MIN_DISPLAY_BESTSELLERS',
    'PREVIOUS_NEXT_IMAGE_HEIGHT',
    'PREVIOUS_NEXT_IMAGE_WIDTH',
    'PREV_NEXT_BAR_LOCATION',
    'PRODUCTS_IMAGE_NO_IMAGE',
    'PRODUCTS_IMAGE_NO_IMAGE_STATUS',
    'PRODUCTS_LIST_PRICE_WIDTH',
    'PRODUCT_INFO_CATEGORIES',
    'PRODUCT_INFO_CATEGORIES_IMAGE_STATUS',
    'PRODUCT_INFO_PREVIOUS_NEXT',
    'PRODUCT_LISTING_COLUMNS_PER_ROW',
    'PRODUCT_LISTING_MULTIPLE_ADD_TO_CART',
    'PRODUCT_LIST_ALPHA_SORTER',
    'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS',
    'PRODUCT_LIST_CATEGORIES_IMAGE_STATUS_TOP',
    'PRODUCT_LIST_CATEGORY_ROW_STATUS',
    'PRODUCT_LIST_DESCRIPTION',
    'PRODUCT_LIST_IMAGE',
    'PRODUCT_LIST_MANUFACTURER',
    'PRODUCT_LIST_MODEL',
    'PRODUCT_LIST_NAME',
    'PRODUCT_LIST_PRICE',
    'PRODUCT_LIST_PRICE_BUY_NOW',
    'PRODUCT_LIST_QUANTITY',
    'PRODUCT_LIST_WEIGHT',
    'SHOW_ACCOUNT_LINKS_ON_SITE_MAP',
    'SHOW_BANNERS_GROUP_SET1',
    'SHOW_BANNERS_GROUP_SET2',
    'SHOW_BANNERS_GROUP_SET3',
    'SHOW_BANNERS_GROUP_SET4',
    'SHOW_BANNERS_GROUP_SET5',
    'SHOW_BANNERS_GROUP_SET6',
    'SHOW_BANNERS_GROUP_SET7',
    'SHOW_BANNERS_GROUP_SET8',
    'SHOW_BANNERS_GROUP_SET_ALL',
    'SHOW_CATEGORIES_BOX_FEATURED_CATEGORIES',
    'SHOW_CATEGORIES_BOX_FEATURED_PRODUCTS',
    'SHOW_CATEGORIES_BOX_PRODUCTS_ALL',
    'SHOW_CATEGORIES_BOX_PRODUCTS_NEW',
    'SHOW_CATEGORIES_BOX_SPECIALS',
    'SHOW_CATEGORIES_SEPARATOR_LINK',
    'SHOW_CATEGORIES_SUBCATEGORIES_ALWAYS',
    'SHOW_CUSTOMER_GREETING',
    'SHOW_FOOTER_IP',
    'SHOW_PREVIOUS_NEXT_IMAGES',
    'SHOW_PREVIOUS_NEXT_STATUS',
    'SHOW_PRODUCTS_SOLD_OUT_IMAGE',
    'SHOW_PRODUCT_INFO_COLUMNS_ALSO_PURCHASED_PRODUCTS',
    'SHOW_PRODUCT_INFO_COLUMNS_FEATURED_PRODUCTS',
    'SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS',
    'SHOW_PRODUCT_INFO_COLUMNS_SPECIALS_PRODUCTS',
    'SHOW_SHIPPING_ESTIMATOR_BUTTON',
    'SHOW_SHOPPING_CART_BOX_STATUS',
    'SHOW_SHOPPING_CART_DELETE',
    'SHOW_SHOPPING_CART_UPDATE',
    'SHOW_TOTALS_IN_CART',
    'SMALL_IMAGE_HEIGHT',
    'SMALL_IMAGE_WIDTH',
    'SUBCATEGORY_IMAGE_TOP_HEIGHT',
    'SUBCATEGORY_IMAGE_TOP_WIDTH',
    'USE_SPLIT_LOGIN_MODE',
];

function main(array $argv): int
{
    $repoRoot = realpath(dirname(__DIR__, 2));

    $targetDir = null;
    $apply = false;
    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--apply') {
            $apply = true;
        } elseif ($targetDir === null) {
            $targetDir = $arg;
        }
    }

    if ($targetDir === null || !is_dir($targetDir)) {
        fwrite(STDERR, "Usage: php convert-tplsettings.php <target-dir> [--apply]\n(<target_dir> would be your /includes or a zc_plugins /catalog dir.)\n(Use --apply to write the fixes. Default mode is reporting only.)\nSee source code for more details.\n");
        return 1;
    }

    // Containment check: refuse to touch anything outside the repo, even if a hostile
    // or mistaken path was passed - this is the thing that turns "wrote N files" into
    // "wrote N files in /etc" if --apply is ever combined with a bad target-dir.
    $resolvedTarget = realpath($targetDir);
    if ($resolvedTarget === false || !str_starts_with($resolvedTarget . DIRECTORY_SEPARATOR, $repoRoot . DIRECTORY_SEPARATOR)) {
        fwrite(STDERR, "Refusing to run: <target-dir> must resolve to a path inside the repo ($repoRoot).\n");
        return 1;
    }

    $keySet = array_fill_keys(TPLSETTING_KEYS, true);
    $files = findPhpFiles($targetDir);
    sort($files);

    $totalConverted = 0;
    $filesChanged = 0;
    $flagged = []; // ['file' => ..., 'line' => ..., 'key' => ..., 'reason' => ...]

    foreach ($files as $path) {
        $result = processFile($path, $keySet);

        if ($result['converted'] > 0) {
            $totalConverted += $result['converted'];
            $filesChanged++;
            echo ($apply ? '[applied] ' : '[dry-run] ') . "$path: {$result['converted']} converted\n";
            if ($apply) {
                file_put_contents($path, $result['output']);
            }
        }

        foreach ($result['flagged'] as $flag) {
            $flag['file'] = $path;
            $flagged[] = $flag;
        }
    }

    echo "\n--- Summary ---\n";
    echo "Files changed: $filesChanged\n";
    echo "Occurrences converted: $totalConverted\n";
    echo 'Occurrences flagged for manual review: ' . count($flagged) . "\n";

    if (!empty($flagged)) {
        echo "\n--- Flagged occurrences (not converted - needs manual attention) ---\n";
        foreach ($flagged as $flag) {
            echo "{$flag['file']}:{$flag['line']}\t{$flag['key']}\t{$flag['reason']}\n";
        }
    }

    if (!$apply && ($totalConverted > 0 || !empty($flagged))) {
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
 * @param array<string, true> $keySet
 * @return array{output: string, converted: int, flagged: list<array{line: int, key: string, reason: string}>}
 */
function processFile(string $path, array $keySet): array
{
    $src = file_get_contents($path);
    $tokens = token_get_all($src);
    $count = count($tokens);

    $funcDepthStack = buildFunctionScopeMap($tokens);

    $out = '';
    $converted = 0;
    $flagged = [];
    $depth = 0;
    $funcStackPos = 0; // index into a replay of the same brace-depth bookkeeping

    // Re-walk to know, at any token index, whether we're inside a function/method body.
    // (buildFunctionScopeMap already computed line->bool; this just keeps $out assembly
    // and that lookup in the same pass for simplicity.)
    $inFunctionForLine = $funcDepthStack;

    for ($i = 0; $i < $count; $i++) {
        $tok = $tokens[$i];

        // --- zen_config('KEY'[, $default]) ---
        if (is_array($tok) && $tok[0] === T_STRING && $tok[1] === 'zen_config') {
            $match = matchZenConfigCall($tokens, $i, $count, $keySet);
            if ($match !== null) {
                $line = $tok[2];
                $key = $match['key'];
                if (!empty($inFunctionForLine[$line])) {
                    // Leave the original source untouched for this whole call span.
                    for ($s = $i; $s <= $match['endIndex']; $s++) {
                        $out .= tokenText($tokens[$s]);
                    }
                    $flagged[] = [
                        'line' => $line,
                        'key' => $key,
                        'reason' => 'inside a function/method body - add `global $tplSetting;` there, then re-run',
                    ];
                } else {
                    if ($match['hasDefault']) {
                        $out .= '($tplSetting->' . $key . ' ?? ' . $match['defaultSrc'] . ')';
                        $flagged[] = [
                            'line' => $line,
                            'key' => $key,
                            'reason' => 'had a zen_config() default argument - converted to ?? null-coalesce; review whether it can be simplified to a bare $tplSetting->' . $key,
                        ];
                    } else {
                        $out .= '$tplSetting->' . $key;
                    }
                    $converted++;
                }
                $i = $match['endIndex'];
                continue;
            }
        }

        // --- bare CONSTANT_NAME (no zen_config() wrapper) ---
        if (is_array($tok) && $tok[0] === T_STRING && isset($keySet[$tok[1]])) {
            $key = $tok[1];
            $line = $tok[2];

            $prevSignificant = previousSignificantToken($tokens, $i);
            $nextSignificant = nextSignificantToken($tokens, $i, $count);

            $isPropertyOrConstAccess = $prevSignificant !== null
                && (tokenText($prevSignificant) === '->' || tokenText($prevSignificant) === '::');
            $isFunctionCall = $nextSignificant !== null && tokenText($nextSignificant) === '(';

            if (!$isPropertyOrConstAccess && !$isFunctionCall) {
                if (!empty($inFunctionForLine[$line])) {
                    $out .= $tok[1];
                    $flagged[] = [
                        'line' => $line,
                        'key' => $key,
                        'reason' => 'inside a function/method body - add `global $tplSetting;` there, then re-run',
                    ];
                } else {
                    $out .= '$tplSetting->' . $key;
                    $converted++;
                }
                continue;
            }
        }

        $out .= is_array($tok) ? $tok[1] : $tok;
    }

    return ['output' => $out, 'converted' => $converted, 'flagged' => $flagged];
}

/**
 * Returns a [line_number => bool] map: true if that line sits inside a function or
 * method body (closures included), false/absent if it's top-level/procedural scope.
 *
 * @param list<mixed> $tokens
 * @return array<int, bool>
 */
function buildFunctionScopeMap(array $tokens): array
{
    $depth = 0;
    $funcDepthStack = [];
    $pendingFunc = false;
    $lineIsInFunction = [];

    foreach ($tokens as $tok) {
        if (is_array($tok)) {
            [$id, , $line] = $tok;
            if ($id === T_FUNCTION) {
                $pendingFunc = true;
            }
            $lineIsInFunction[$line] = !empty($funcDepthStack);
            continue;
        }

        if ($tok === '{') {
            $depth++;
            if ($pendingFunc) {
                $funcDepthStack[] = $depth;
                $pendingFunc = false;
            }
        } elseif ($tok === '}') {
            if (!empty($funcDepthStack) && end($funcDepthStack) === $depth) {
                array_pop($funcDepthStack);
            }
            $depth--;
        }
    }

    return $lineIsInFunction;
}

/**
 * If $tokens[$i] is `zen_config` immediately followed by `(`, a string-literal key matching
 * one of $keySet, optionally `, <default expr>`, then `)` - returns match details. Otherwise null.
 *
 * @param list<mixed> $tokens
 * @param array<string, true> $keySet
 * @return array{key: string, hasDefault: bool, defaultSrc: string, endIndex: int}|null
 */
function matchZenConfigCall(array $tokens, int $i, int $count, array $keySet): ?array
{
    $j = skipWhitespace($tokens, $i + 1, $count);
    if ($j >= $count || tokenText($tokens[$j]) !== '(') {
        return null;
    }
    $openParenIdx = $j;

    $j = skipWhitespace($tokens, $j + 1, $count);
    if ($j >= $count || !is_array($tokens[$j]) || $tokens[$j][0] !== T_CONSTANT_ENCAPSED_STRING) {
        return null;
    }
    $key = substr($tokens[$j][1], 1, -1);
    if (!isset($keySet[$key])) {
        return null;
    }
    $afterKeyIdx = $j;

    // Find the matching closing ')', tracking nested parens.
    $depth = 1;
    $k = $openParenIdx + 1;
    for (; $k < $count; $k++) {
        $val = tokenText($tokens[$k]);
        if ($val === '(') {
            $depth++;
        } elseif ($val === ')') {
            $depth--;
            if ($depth === 0) {
                break;
            }
        }
    }
    $closeParenIdx = $k;

    // Anything between the key string and the closing paren (other than a leading comma
    // and surrounding whitespace) is the default-value expression.
    $defaultTokens = array_slice($tokens, $afterKeyIdx + 1, $closeParenIdx - $afterKeyIdx - 1);
    $defaultSrc = '';
    $sawComma = false;
    foreach ($defaultTokens as $t) {
        $text = tokenText($t);
        if (!$sawComma) {
            if ($text === ',') {
                $sawComma = true;
            }
            continue;
        }
        $defaultSrc .= $text;
    }
    $defaultSrc = trim($defaultSrc);

    return [
        'key' => $key,
        'hasDefault' => $defaultSrc !== '',
        'defaultSrc' => $defaultSrc,
        'endIndex' => $closeParenIdx,
    ];
}

function skipWhitespace(array $tokens, int $i, int $count): int
{
    while ($i < $count && is_array($tokens[$i]) && $tokens[$i][0] === T_WHITESPACE) {
        $i++;
    }
    return $i;
}

function previousSignificantToken(array $tokens, int $i): mixed
{
    for ($j = $i - 1; $j >= 0; $j--) {
        if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            continue;
        }
        return $tokens[$j];
    }
    return null;
}

function nextSignificantToken(array $tokens, int $i, int $count): mixed
{
    for ($j = $i + 1; $j < $count; $j++) {
        if (is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            continue;
        }
        return $tokens[$j];
    }
    return null;
}

function tokenText(mixed $tok): string
{
    return is_array($tok) ? $tok[1] : $tok;
}

exit(main($argv));
