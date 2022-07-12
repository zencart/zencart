<?php
/**
 * polyfills to accommodate older PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions  (c) Fabien Potencier <fabien@symfony.com>
 *
 * @version $Id: DrByte 2021 Jan 11 New in v1.5.8-alpha $
 */


if (PHP_VERSION_ID >= 80100) {
    return;
}
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
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
    function fdiv(float $dividend, float $divisor)
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
            case PREG_JIT_STACKLIMIT_ERROR:
                return 'JIT stack limit exhausted';
            case PREG_NO_ERROR:
                return 'No error';
            default:
                return 'Unknown error';
        }
    }
}
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle)
    {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle)
    {
        return 0 === \strncmp($haystack, $needle, \strlen($needle));
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle)
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
if (!function_exists('get_resource_id')) {
    function get_resource_id($res)
    {
        if (!\is_resource($res) && null === @get_resource_type($res)) {
            throw new \TypeError(sprintf('Argument 1 passed to get_resource_id() must be of the type resource, %s given', get_debug_type($res)));
        }

        return (int) $res;
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
