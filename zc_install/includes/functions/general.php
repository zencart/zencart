<?php
/**
 * zc_install general functions
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Oct 17 Modified in v2.1.0 $
 */

if (!defined('TABLE_UPGRADE_EXCEPTIONS')) {
    define('TABLE_UPGRADE_EXCEPTIONS', 'upgrade_exceptions');
}

function zen_get_select_options(array $optionList, string|int $setDefault): string
{
    $optionString = "";
    foreach ($optionList as $option) {
        $optionString .= '<option value="' . $option['id'] . '"';
        if ((string)$setDefault === (string)$option['id']) {
            $optionString .= " selected ";
        }
        $optionString .= '>' . $option['text'];
        $optionString .= '</option>';
    }
    return $optionString;
}

function logDetails(string $details, string $location = "General"): void
{
    if (!isset($_SESSION['logfilename']) || $_SESSION['logfilename'] === '') {
        $_SESSION['logfilename'] = date('m-d-Y_h-i-s-') . zen_create_random_value(6);
    }
    if ($fp = @fopen(DEBUG_LOG_FOLDER . '/zcInstallLog_' . $_SESSION['logfilename'] . '.log', 'a')) {
        fwrite($fp, '---------------' . "\n" . date('M d Y G:i') . ' -- ' . $location . "\n" . $details . "\n\n");
        fclose($fp);
    }
}

function zen_rand(?int $min = null, ?int $max = null): int
{
    static $seeded;

    if (!isset($seeded)) {
        mt_srand((int)(microtime(true) * 1000000));
        $seeded = true;
    }

    if (isset($min, $max)) {
        if ($min >= $max) {
            return $min;
        }
        return random_int($min, $max);
    }

    return mt_rand();
}

function zen_get_document_root(): string
{
    $dir_fs_www_root = realpath(dirname(basename(__FILE__)) . "/..");
    if ($dir_fs_www_root === '') {
        $dir_fs_www_root = '/';
    }
    return str_replace(['\\', '//'], '/', $dir_fs_www_root);
}

function zen_get_http_server(): string
{
    $host = $_SERVER['HTTP_HOST'];
    $script = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
    if (str_starts_with($script[0], '~')) {
        $host .= '/' . $script[0];
    }
    return $host;
}

function zen_parse_url(string $url, string $element = 'array', bool $detect_tilde = false): mixed
{
    // Read the various elements of the URL, to use in auto-detection of admin foldername (basically a simplified parse_url equivalent which automatically supports ports and uncommon TLDs)
    $parsed = [];
    // scheme
    $segment1 = explode('://', $url);
    $parsed['scheme'] = $segment1[0];
    // host
    $segment2 = explode('/', trim($segment1[1], '/'));
    $parsed['host'] = $segment2[0];
    array_shift($segment2);
    // adjust host to accommodate /~username shared-ssl scenarios
    if ($detect_tilde && isset($segment2[0]) && str_starts_with($segment2[0], '~')) {
        $parsed['host'] .= '/' . $segment2[0];
        // array_shift also therefore removes it from ['path'] below
        array_shift($segment2);
    }
    // path/uri
    $parsed['path'] = implode('/', $segment2);
    $path = ($parsed['path'] !== '') ? '/' . $parsed['path'] : '';

    return match ($element) {
        'scheme', 'host', 'path' => $parsed[$element],
        '/path' => $path,
        default => $parsed,
    };
}

function zen_sanitize_request(): void
{
    foreach ($_POST as $key => $value) {
        $_POST[htmlspecialchars($key, ENT_COMPAT, 'UTF-8', false)] = addslashes($value);
    }
}

/**
 * Returns a string with conversions for security.
 *
 * Runs htmlspecialchars over the string
 */
function zen_output_string_protected(string $string): string
{
    return htmlspecialchars($string, ENT_COMPAT, 'utf-8', true);
}

function zen_get_install_languages_list(string $lng): string
{
    global $languagesInstalled;
    $optionString = "";
    foreach ($languagesInstalled as $code => $language) {
        $optionString .= '<option value="' . $code . '"';
        if ((string)$code === $lng) {
            $optionString .= " selected ";
        }
        $optionString .= '>' . $language['displayName'];
        $optionString .= "</option>";
    }
    return $optionString;
}

/**
 * helper function to detect current site URI info
 * @return array($adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog)
 */
function getDetectedURIs($adminDir = 'admin'): array
{
    global $request_type;
    if (isset($_POST['adminDir'])) {
        $adminDir = zen_output_string_protected($_POST['adminDir']);
    }
    $documentRoot = zen_get_document_root();
    $url = ($request_type === 'SSL' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . str_replace('/zc_install/index.php', '', $_SERVER['SCRIPT_NAME']);
    $httpServer = zen_parse_url($url, 'host', true);
    $adminServer = ($request_type === 'SSL') ? 'https://' : 'http://';
    $adminServer .= $httpServer;
    $catalogHttpServer = ($request_type === 'SSL' ? 'https://' : 'http://') . $httpServer;
    $catalogHttpUrl = ($request_type === 'SSL' ? 'https://' : 'http://') . $httpServer . '/' . zen_parse_url($url, 'path', true);
    $catalogHttpsServer = 'https://' . $httpServer;
    $catalogHttpsUrl = 'https://' . $httpServer . '/' . zen_parse_url($url, 'path', true);
    $dir_ws_http_catalog = str_replace($catalogHttpServer, '', $catalogHttpUrl) . '/';
    $dir_ws_https_catalog = str_replace($catalogHttpsServer, '', $catalogHttpsUrl) . '/';

    return [$adminDir, $documentRoot, $adminServer, $catalogHttpServer, $catalogHttpUrl, $catalogHttpsServer, $catalogHttpsUrl, $dir_ws_http_catalog, $dir_ws_https_catalog];
}


