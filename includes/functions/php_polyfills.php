<?php
/**
 * polyfills to accommodate older PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions  (c) Fabien Potencier <fabien@symfony.com>
 *
 * @version $Id: DrByte 2023 Aug 01 Modified in v2.0.0-alpha1 $
 */

if (\PHP_VERSION_ID >= 80400) {
    return;
}
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst(string $string, ?string $encoding = null): string
    {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        try {
            $validEncoding = @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('mb_ucfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        // BC for PHP 7.3 and lower
        if (!$validEncoding) {
            throw new \ValueError(sprintf('mb_ucfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        $firstChar = mb_substr($string, 0, 1, $encoding);
        $firstChar = mb_convert_case($firstChar, MB_CASE_TITLE, $encoding);

        return $firstChar . mb_substr($string, 1, null, $encoding);
    }
}

if (!function_exists('mb_lcfirst')) {
    function mb_lcfirst(string $string, ?string $encoding = null): string
    {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        try {
            $validEncoding = @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('mb_lcfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        // BC for PHP 7.3 and lower
        if (!$validEncoding) {
            throw new \ValueError(sprintf('mb_lcfirst(): Argument #2 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        $firstChar = mb_substr($string, 0, 1, $encoding);
        $firstChar = mb_convert_case($firstChar, MB_CASE_LOWER, $encoding);

        return $firstChar . mb_substr($string, 1, null, $encoding);
    }
}

if (\PHP_VERSION_ID >= 80300) {
    return;
}
if (!function_exists('json_validate')) {
    if (!defined('JSON_MAX_DEPTH')) define('JSON_MAX_DEPTH', 0x7FFFFFFF); // see https://www.php.net/manual/en/function.json-decode.php

    function json_validate(string $json, int $depth = 512, int $flags = 0): bool
    {
        if (0 !== $flags && \defined('JSON_INVALID_UTF8_IGNORE') && \JSON_INVALID_UTF8_IGNORE !== $flags) {
            throw new \ValueError('json_validate(): Argument #3 ($flags) must be a valid flag (allowed flags: JSON_INVALID_UTF8_IGNORE)');
        }

        if ($depth <= 0) {
            throw new \ValueError('json_validate(): Argument #2 ($depth) must be greater than 0');
        }

        if ($depth > JSON_MAX_DEPTH) {
            throw new \ValueError(sprintf('json_validate(): Argument #2 ($depth) must be less than %d', JSON_MAX_DEPTH));
        }

        json_decode($json, null, $depth, $flags);

        return \JSON_ERROR_NONE === json_last_error();
    }
}

if (!function_exists('mb_str_pad') && function_exists('mb_substr')) {
    function mb_str_pad(string $string, int $length, string $pad_string = ' ', int $pad_type = \STR_PAD_RIGHT, string $encoding = null): string
    {
        if (!\in_array($pad_type, [\STR_PAD_RIGHT, \STR_PAD_LEFT, \STR_PAD_BOTH], true)) {
            throw new \ValueError('mb_str_pad(): Argument #4 ($pad_type) must be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH');
        }

        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        try {
            $validEncoding = @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('mb_str_pad(): Argument #5 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        // BC for PHP 7.3 and lower
        if (!$validEncoding) {
            throw new \ValueError(sprintf('mb_str_pad(): Argument #5 ($encoding) must be a valid encoding, "%s" given', $encoding));
        }

        if (mb_strlen($pad_string, $encoding) <= 0) {
            throw new \ValueError('mb_str_pad(): Argument #3 ($pad_string) must be a non-empty string');
        }

        $paddingRequired = $length - mb_strlen($string, $encoding);

        if ($paddingRequired < 1) {
            return $string;
        }

        switch ($pad_type) {
            case \STR_PAD_LEFT:
                return mb_substr(str_repeat($pad_string, $paddingRequired), 0, $paddingRequired, $encoding).$string;
            case \STR_PAD_RIGHT:
                return $string.mb_substr(str_repeat($pad_string, $paddingRequired), 0, $paddingRequired, $encoding);
            default:
                $leftPaddingLength = floor($paddingRequired / 2);
                $rightPaddingLength = $paddingRequired - $leftPaddingLength;

                return mb_substr(str_repeat($pad_string, $leftPaddingLength), 0, $leftPaddingLength, $encoding).$string.mb_substr(str_repeat($pad_string, $rightPaddingLength), 0, $rightPaddingLength, $encoding);
        }
    }
}

if (\PHP_VERSION_ID >= 80100) {
    return;
}
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool
    {
        if ([] === $array || $array === array_values($array)) {
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
if (!function_exists('enum_exists')) {
    function enum_exists(string $enum, bool $autoload = true): bool { return $autoload && class_exists($enum) && false; }
}


if (\PHP_VERSION_ID >= 80000) {
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



if (\PHP_VERSION_ID >= 70300) {
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
