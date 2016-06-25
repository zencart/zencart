<?php
namespace Aura\Web;

function header(
    $string,
    $replace = true,
    $http_response_code = null
) {
    FakeResponseSender::$headers[] = func_get_args();
}

function setcookie(
    $name,
    $value,
    $expire,
    $path,
    $domain,
    $secure,
    $httponly
) {
    FakeResponseSender::$cookies[] = func_get_args();
}

class FakeResponseSender extends ResponseSender
{
    public static $headers = array();
    public static $cookies = array();
    public static $content;

    public static function reset()
    {
        static::$headers = array();
        static::$cookies = array();
        static::$content = null;
    }

    public function __invoke()
    {
        ob_start();
        parent::__invoke();
        self::$content = ob_get_clean();
    }
}
