<?php
/**
 * zc_install general functions
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte   Modified in v2.3.0 $
 */

const ZC_INSTALL_SESSION_NAME = 'zenInstallerId';
const ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY = 'zcInstallUpgradeAuth';
const ZC_INSTALL_UPGRADE_AUTH_TTL = 600;
const ZC_INSTALL_ADMIN_SETUP_MODE_DIRECTORY = 'directory';
const ZC_INSTALL_ADMIN_SETUP_MODE_ADMIN_USER = 'admin_user';

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
        if (function_exists('mt_srand')) {
            mt_srand((int)(microtime(true) * 1000000));
        }
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

function zc_install_start_installer_session(): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return true;
    }

    if (session_status() === PHP_SESSION_DISABLED) {
        return false;
    }

    session_name(ZC_INSTALL_SESSION_NAME);
    return @session_start();
}

/**
 * @return list<string>
 */
function zc_install_upgrade_versions_for_db_version(?string $dbVersion, array $versionArray): array
{
    if ($dbVersion === null || $dbVersion === '') {
        return [];
    }

    $upgradeableVersions = array_keys($versionArray);
    $key = array_search($dbVersion, $upgradeableVersions, true);
    if ($key === false) {
        return [];
    }

    return array_values(array_slice($upgradeableVersions, $key + 1));
}

function zc_install_create_upgrade_authorization(int $adminId, ?string $dbVersion, array $versionArray): string
{
    $allowedVersions = zc_install_upgrade_versions_for_db_version($dbVersion, $versionArray);
    if (empty($allowedVersions)) {
        return '';
    }

    $nonce = bin2hex(random_bytes(32));
    $_SESSION[ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY] = [
        'admin_id' => $adminId,
        'nonce' => $nonce,
        'db_version' => $dbVersion,
        'allowed_versions' => $allowedVersions,
        'expires_at' => time() + ZC_INSTALL_UPGRADE_AUTH_TTL,
    ];

    return $nonce;
}

function zc_install_is_upgrade_request_authorized(string $nonce, string $updateVersion): bool
{
    $auth = $_SESSION[ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY] ?? null;
    if (!is_array($auth)) {
        return false;
    }

    if (!is_string($auth['nonce'] ?? null) || !hash_equals($auth['nonce'], $nonce)) {
        return false;
    }

    if (!is_int($auth['expires_at'] ?? null) || $auth['expires_at'] < time()) {
        unset($_SESSION[ZC_INSTALL_UPGRADE_AUTH_SESSION_KEY]);
        return false;
    }

    if (!is_array($auth['allowed_versions'] ?? null)) {
        return false;
    }

    return in_array($updateVersion, $auth['allowed_versions'], true);
}

function zc_install_is_safe_admin_directory(string $adminDir): bool
{
    return $adminDir !== ''
        && $adminDir !== '.'
        && $adminDir !== '..'
        && !str_contains($adminDir, '/')
        && !str_contains($adminDir, '\\')
        && !str_contains($adminDir, "\0");
}

function zc_install_admin_setup_request_mode(array $post): string
{
    if (isset($post['admin_user']) || isset($post['admin_email']) || isset($post['admin_email2'])) {
        return ZC_INSTALL_ADMIN_SETUP_MODE_ADMIN_USER;
    }

    return ZC_INSTALL_ADMIN_SETUP_MODE_DIRECTORY;
}

function zc_install_error_text_admin_email(): string
{
    return defined('TEXT_ADMIN_SETUP_MATCHING_EMAIL')
        ? TEXT_ADMIN_SETUP_MATCHING_EMAIL
        : 'A matching valid email address is required.';
}

/**
 * @return array<string, string>
 */
function zc_install_validate_admin_setup_request(array $post): array
{
    $errorList = [];
    $adminDir = $post['adminDir'] ?? null;
    if (!is_string($adminDir) || !zc_install_is_safe_admin_directory(trim($adminDir))) {
        $errorList['adminDir'] = 'Admin directory is required';
    }

    if (zc_install_admin_setup_request_mode($post) === ZC_INSTALL_ADMIN_SETUP_MODE_ADMIN_USER) {
        if (empty($post['admin_user']) || !is_string($post['admin_user'])) {
            $errorList['admin_user'] = 'Username is required';
        }
        if (
            empty($post['admin_email'])
            || empty($post['admin_email2'])
            || !is_string($post['admin_email'])
            || !is_string($post['admin_email2'])
            || $post['admin_email'] !== $post['admin_email2']
            || filter_var($post['admin_email'], FILTER_VALIDATE_EMAIL) === false
        ) {
            $errorList['admin_email2'] = zc_install_error_text_admin_email();
        }

        return $errorList;
    }

    $requiredInstallFields = [
        'action',
        'physical_path',
        'http_server_admin',
        'http_server_catalog',
        'db_type',
        'db_host',
        'db_user',
        'db_name',
        'sql_cache_method',
    ];
    foreach ($requiredInstallFields as $field) {
        if (!isset($post[$field]) || !is_scalar($post[$field]) || trim((string)$post[$field]) === '') {
            $errorList[$field] = 'Required installer field is missing';
        }
    }

    if (($post['action'] ?? '') !== 'process') {
        $errorList['action'] = 'Invalid installer action';
    }

    return $errorList;
}

