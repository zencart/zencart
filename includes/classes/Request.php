<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 */

namespace Zencart\Request;

use Zencart\Traits\Singleton;

/**
 * @since ZC v1.5.8
 */
class Request
{
    use Singleton;

    protected array $paramBag;

    /**
     * @return mixed|Request
     * @since ZC v1.5.8
     */
    public static function capture(): mixed
    {
        $self = self::getInstance();
        $self->paramBag = $_REQUEST;
        return self::getInstance();
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     * @since ZC v1.5.8
     */
    public function input($key, $default = null): mixed
    {
        return $this->paramBag[$key] ?? $default;
    }

    /**
     * @param $key
     * @return bool
     * @since ZC v1.5.8
     */
    public function has($key): bool
    {
        return isset($this->paramBag[$key]);
    }

    public static function isSecure(): bool
    {
        /**
         * Detect the type of request received (secure or not)
         */
        return ((isset($_SERVER['HTTPS']) && (strtolower((string)$_SERVER['HTTPS']) !== 'off' || $_SERVER['HTTPS'] == '1'))) ||
            (isset($_SERVER['HTTP_X_FORWARDED_BY']) && str_contains(strtoupper((string)$_SERVER['HTTP_X_FORWARDED_BY']), 'SSL')) ||
            (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && (str_contains(strtoupper((string)$_SERVER['HTTP_X_FORWARDED_HOST']), 'SSL') || str_contains(strtolower((string)$_SERVER['HTTP_X_FORWARDED_HOST']), str_replace('https://', '', HTTP_SERVER)))) ||
            (isset($_SERVER['HTTP_X_FORWARDED_SERVER']) && str_contains(strtolower((string)$_SERVER['HTTP_X_FORWARDED_SERVER']), str_replace('https://', '', HTTP_SERVER))) ||
            (isset($_SERVER['SCRIPT_URI']) && stripos((string)$_SERVER['SCRIPT_URI'], 'https:') === 0) ||
            (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && ($_SERVER['HTTP_X_FORWARDED_SSL'] == '1' || strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && (strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'ssl' || strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')) ||
            (isset($_SERVER['HTTP_SSLSESSIONID']) && $_SERVER['HTTP_SSLSESSIONID'] !== '') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == '443') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');
    }
}
