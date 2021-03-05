<?php
/**
 * polyfills to accommodate older PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions  (c) Fabien Potencier <fabien@symfony.com>
 *
 * @version $Id: DrByte 2021 Feb 25 New in v1.5.7c $
 */


if (PHP_VERSION_ID >= 80100) {
    return;
}
if (!function_exists('array_is_list')) {
    function array_is_list(array $array)
    {
        if ([] === $array) {
            return true;
        }

        $nextKey = -1;

        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                return false;
            }
        }

        return true;
    }
}



if (PHP_VERSION_ID >= 80000) {
    return;
}
if (!defined('FILTER_VALIDATE_BOOL') && defined('FILTER_VALIDATE_BOOLEAN')) {
    define('FILTER_VALIDATE_BOOL', FILTER_VALIDATE_BOOLEAN);
}
if (!function_exists('fdiv')) {
    function fdiv($dividend, $divisor)
    {
        return @($dividend / $divisor);
    }
}
if (!function_exists('preg_last_error_msg')) {
    function preg_last_error_msg()
    {
        switch (preg_last_error()) {
            case PREG_INTERNAL_ERROR:
                return 'Internal error';
            case PREG_BAD_UTF8_ERROR:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            case PREG_BAD_UTF8_OFFSET_ERROR:
                return 'The offset did not correspond to the beginning of a valid UTF-8 code point';
            case PREG_BACKTRACK_LIMIT_ERROR:
                return 'Backtrack limit exhausted';
            case PREG_RECURSION_LIMIT_ERROR:
                return 'Recursion limit exhausted';
            case PREG_NO_ERROR:
                return 'No error';
            default:
                return 'Unknown error';
        }
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return 0 === \strncmp($haystack, $needle, \strlen($needle));
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return '' === $needle || ('' !== $haystack && 0 === \substr_compare($haystack, $needle, -\strlen($needle)));
    }
}
if (!function_exists('get_debug_type')) {
    function get_debug_type($value)
    {
        switch (true) {
            case null === $value: return 'null';
            case \is_bool($value): return 'bool';
            case \is_string($value): return 'string';
            case \is_array($value): return 'array';
            case \is_int($value): return 'int';
            case \is_float($value): return 'float';
            case \is_object($value): break;
            case $value instanceof \__PHP_Incomplete_Class: return '__PHP_Incomplete_Class';
            default:
                if (null === $type = @get_resource_type($value)) {
                    return 'unknown';
                }

                if ('Unknown' === $type) {
                    $type = 'closed';
                }

                return "resource ($type)";
        }

        $class = \get_class($value);

        if (false === strpos($class, '@')) {
            return $class;
        }

        return (get_parent_class($class) ?: key(class_implements($class)) ?: 'class').'@anonymous';
    }
}



if (PHP_VERSION_ID >= 70300) {
    return;
}
if (!function_exists('is_countable')) {
    function is_countable($value) { return is_array($value) || $value instanceof Countable || $value instanceof ResourceBundle || $value instanceof SimpleXmlElement; }
}
if (!function_exists('array_key_first')) {
    function array_key_first(array $array) { foreach ($array as $key => $value) { return $key; } }
}
if (!function_exists('array_key_last')) {
    function array_key_last(array $array) { return key(array_slice($array, -1, 1, true)); }
}



if (PHP_VERSION_ID >= 70200) {
    return;
}
if (!defined('PHP_FLOAT_DIG')) {
    define('PHP_FLOAT_DIG', 15);
}
if (!defined('PHP_FLOAT_EPSILON')) {
    define('PHP_FLOAT_EPSILON', 2.2204460492503E-16);
}
if (!defined('PHP_FLOAT_MIN')) {
    define('PHP_FLOAT_MIN', 2.2250738585072E-308);
}
if (!defined('PHP_FLOAT_MAX')) {
    define('PHP_FLOAT_MAX', 1.7976931348623157E+308);
}
if (!defined('PHP_OS_FAMILY')) {
    function php_os_family()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return 'Windows';
        }

        $map = array(
            'Darwin' => 'Darwin',
            'DragonFly' => 'BSD',
            'FreeBSD' => 'BSD',
            'NetBSD' => 'BSD',
            'OpenBSD' => 'BSD',
            'Linux' => 'Linux',
            'SunOS' => 'Solaris',
        );

        return isset($map[PHP_OS]) ? $map[PHP_OS] : 'Unknown';
    }

    define('PHP_OS_FAMILY', php_os_family());
}
if (!function_exists('utf8_encode')) {
    function utf8_encode($s)
    {
        $s .= $s;
        $len = \strlen($s);

        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
            switch (true) {
                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
                default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
            }
        }

        return substr($s, 0, $j);
    }
}
if (!function_exists('utf8_decode')) {
    function utf8_decode($s)
    {
        $s = (string) $s;
        $len = \strlen($s);

        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($s[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    $c = (\ord($s[$i] & "\x1F") << 6) | \ord($s[++$i] & "\x3F");
                    $s[$j] = $c < 256 ? \chr($c) : '?';
                    break;

                case "\xF0":
                    ++$i;
                    // no break

                case "\xE0":
                    $s[$j] = '?';
                    $i += 2;
                    break;

                default:
                    $s[$j] = $s[$i];
            }
        }

        return substr($s, 0, $j);
    }
}



if (PHP_VERSION_ID >= 70100) {
    return;
}
if (!function_exists('is_iterable')) {
    function is_iterable($value)
    {
        return \is_array($value) || $value instanceof \Traversable;
    }
}



if (PHP_VERSION_ID >= 70000) {
    return;
}
if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}
if (!defined('PREG_JIT_STACKLIMIT_ERROR')) {
    define('PREG_JIT_STACKLIMIT_ERROR', 6);
}
if (!function_exists('error_clear_last')) {
    function error_clear_last()
    {
        static $handler;
        if (!$handler) {
            $handler = function () { return false; };
        }
        set_error_handler($handler);
        @trigger_error('');
        restore_error_handler();
    }
}
