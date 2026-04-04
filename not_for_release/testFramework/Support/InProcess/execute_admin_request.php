<?php

use Tests\Support\InProcess\FeatureRequest;
use Tests\Support\InProcess\InProcessRedirectException;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This runner may only be executed from CLI.\n");
    exit(1);
}

$requestFile = $argv[1] ?? null;
$responseFile = $argv[2] ?? null;

if ($requestFile === null || $responseFile === null) {
    fwrite(STDERR, "Usage: php execute_admin_request.php <request-json> <response-json>\n");
    exit(1);
}

$payload = json_decode((string) file_get_contents($requestFile), true);
if (!is_array($payload)) {
    fwrite(STDERR, "Invalid request payload.\n");
    exit(1);
}

require $payload['root_cwd'] . 'vendor/autoload.php';

$normalizeProcessLocale = static function (): void {
    $detectedLocale = setlocale(LC_TIME, '0');
    $normalizedLocale = strtoupper((string) $detectedLocale);

    if ($detectedLocale === false || in_array($normalizedLocale, ['C', 'C.UTF-8', 'C.UTF8'], true)) {
        setlocale(LC_TIME, ['en_US', 'en_US.UTF-8', 'en_US.utf8', 'en-US', 'en']);
    }
};

$normalizeProcessLocale();

global $template, $current_page_base;
$current_page_base = 'home';
if (!defined('ICON_IMAGE_ERROR')) {
    define('ICON_IMAGE_ERROR', 'icon_error.gif');
}
if (!defined('ICON_IMAGE_SUCCESS')) {
    define('ICON_IMAGE_SUCCESS', 'icon_success.gif');
}
if (!defined('ICON_IMAGE_WARNING')) {
    define('ICON_IMAGE_WARNING', 'icon_warning.gif');
}
if (!defined('ICON_ERROR_ALT')) {
    define('ICON_ERROR_ALT', 'Error');
}
if (!defined('ICON_SUCCESS_ALT')) {
    define('ICON_SUCCESS_ALT', 'Success');
}
if (!defined('ICON_WARNING_ALT')) {
    define('ICON_WARNING_ALT', 'Warning');
}
if (!function_exists('zen_image')) {
    function zen_image($src, $alt = '', $width = '', $height = '', $params = ''): string
    {
        return '';
    }
}
if (!isset($template) || !is_object($template)) {
    $template = new class {
        public function get_template_dir($template, $baseDir, $page, $type): string
        {
            return 'includes/templates/template_default';
        }
    };
}

$request = new FeatureRequest(
    $payload['uri'],
    $payload['method'],
    $payload['query'] ?? [],
    $payload['request'] ?? [],
    $payload['server'] ?? [],
    $payload['cookies'] ?? []
);

$entrypoint = $payload['entrypoint'];
$documentRoot = rtrim($payload['document_root'], '/');
$capturedHeaders = [];

$normalizeHeaders = static function (array $headers): array {
    $normalized = [];

    foreach ($headers as $header) {
        $position = strpos($header, ':');
        if ($position === false) {
            continue;
        }

        $normalized[trim(substr($header, 0, $position))] = trim(substr($header, $position + 1));
    }

    return $normalized;
};

$extractResponseCookies = static function (array $headers): array {
    $cookies = [];

    foreach ($headers as $header) {
        if (stripos($header, 'Set-Cookie:') !== 0) {
            continue;
        }

        $cookieLine = trim(substr($header, strlen('Set-Cookie:')));
        $cookiePair = strtok($cookieLine, ';');
        if ($cookiePair === false || $cookiePair === '') {
            continue;
        }

        [$cookieName, $cookieValue] = array_pad(explode('=', $cookiePair, 2), 2, '');
        $cookieName = trim($cookieName);
        if ($cookieName === '') {
            continue;
        }

        $cookies[$cookieName] = trim($cookieValue);
    }

    return $cookies;
};

$writeResponse = static function () use ($responseFile, $normalizeHeaders, $extractResponseCookies, &$capturedHeaders): void {
    $content = '';
    if (ob_get_level() > 0) {
        $content = (string) ob_get_contents();
    }

    $rawHeaders = headers_list() ?: [];
    $headers = $normalizeHeaders($rawHeaders);
    if ($capturedHeaders !== []) {
        $headers = array_merge($headers, $capturedHeaders);
    }

    $response = [
        'status_code' => http_response_code() ?: 200,
        'content' => $content,
        'headers' => $headers,
        'cookies' => $extractResponseCookies($rawHeaders),
        'last_error' => error_get_last(),
    ];

    if (function_exists('session_name') && function_exists('session_id')) {
        $sessionName = session_name();
        $sessionId = session_id();
        if ($sessionName !== '' && $sessionId !== '') {
            $response['cookies'][$sessionName] = $sessionId;
        }
    }

    file_put_contents($responseFile, json_encode($response));
};

register_shutdown_function($writeResponse);

$query = $request->queryParameters();
$_GET = $query;
$_POST = $request->request;
$_REQUEST = array_merge($_GET, $_POST);
$_COOKIE = $request->cookies;
$_FILES = [];

$requestUri = $request->requestPath();
if (!empty($query)) {
    $requestUri .= '?' . http_build_query($query);
}

$_SERVER = array_merge([
    'DOCUMENT_ROOT' => $documentRoot,
    'HTTP_HOST' => 'localhost',
    'HTTPS' => 'off',
    'REMOTE_ADDR' => '127.0.0.1',
    'REQUEST_METHOD' => strtoupper($request->method),
    'REQUEST_URI' => $requestUri,
    'SCRIPT_FILENAME' => $entrypoint,
    'SCRIPT_NAME' => '/admin/index.php',
    'PHP_SELF' => '/admin/index.php',
    'SERVER_NAME' => 'localhost',
    'SERVER_PORT' => '80',
], $request->server);

if (!isset($_SESSION)) {
    $_SESSION = [];
}

if (!defined('ROOTCWD')) {
    define('ROOTCWD', rtrim($payload['root_cwd'], '/') . '/');
}
if (!defined('TESTCWD')) {
    define('TESTCWD', ROOTCWD . 'not_for_release/testFramework/');
}
if (!defined('ZENCART_TESTFRAMEWORK_RUNNING')) {
    define('ZENCART_TESTFRAMEWORK_RUNNING', true);
}
if (!defined('ZENCART_INPROCESS_REDIRECT_CAPTURE')) {
    define('ZENCART_INPROCESS_REDIRECT_CAPTURE', true);
}
if (function_exists('header_remove') && !headers_sent()) {
    header_remove();
}

http_response_code(200);
chdir($documentRoot . '/admin');

ob_start();

try {
    require $entrypoint;
} catch (InProcessRedirectException $exception) {
    http_response_code($exception->statusCode);
    $capturedHeaders['Location'] = $exception->url;
}
