<?php
/**
 * polyfills to accommodate older PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions  (c) Fabien Potencier <fabien@symfony.com>
 *
 * @version $Id: DrByte 2024 Sep 02 Modified in v2.1.0-beta1 $
 */

if (\PHP_VERSION_ID >= 80400) {
    return;
}
if (!function_exists('array_find')) {
    function array_find(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return null;
    }
}

if (!function_exists('array_find_key')) {
    function array_find_key(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $key;
            }
        }

        return null;
    }
}

if (!function_exists('array_any')) {
    function array_any(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('array_all')) {
    function array_all(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (!$callback($value, $key)) {
                return false;
            }
        }

        return true;
    }
}


if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
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

        $firstChar = mb_substr($string, 0, 1, $encoding);
        $firstChar = mb_convert_case($firstChar, MB_CASE_TITLE, $encoding);

        return $firstChar . mb_substr($string, 1, null, $encoding);
    }
}

if (!function_exists('mb_lcfirst') && function_exists('mb_substr')) {
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

        $firstChar = mb_substr($string, 0, 1, $encoding);
        $firstChar = mb_convert_case($firstChar, MB_CASE_LOWER, $encoding);

        return $firstChar . mb_substr($string, 1, null, $encoding);
    }
}

if (!function_exists('mb_internal_trim') && !function_exists('mb_trim') && function_exists('mb_convert_encoding')) {
    /** Polyfill helper function, not to be called directly */
    function mb_internal_trim(string $regex, string $string, ?string $characters, ?string $encoding, string $function): string
    {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        try {
            $validEncoding = @mb_check_encoding('', $encoding);
        } catch (\ValueError $e) {
            throw new \ValueError(sprintf('%s(): Argument #3 ($encoding) must be a valid encoding, "%s" given', $function, $encoding));
        }

        if ('' === $characters) {
            return null === $encoding ? $string : mb_convert_encoding($string, $encoding);
        }

        if ('UTF-8' === $encoding || \in_array(strtolower($encoding), ['utf-8', 'utf8'], true)) {
            $encoding = 'UTF-8';
        }

        $string = mb_convert_encoding($string, 'UTF-8', $encoding);

        if (null !== $characters) {
            $characters = mb_convert_encoding($characters, 'UTF-8', $encoding);
        }

        if (null === $characters) {
            $characters = "\\0 \f\n\r\t\v\u{00A0}\u{1680}\u{2000}\u{2001}\u{2002}\u{2003}\u{2004}\u{2005}\u{2006}\u{2007}\u{2008}\u{2009}\u{200A}\u{2028}\u{2029}\u{202F}\u{205F}\u{3000}\u{0085}\u{180E}";
        } else {
            $characters = preg_quote($characters);
        }

        $string = preg_replace(sprintf($regex, $characters), '', $string);

        if ('UTF-8' === $encoding) {
            return $string;
        }

        return mb_convert_encoding($string, $encoding, 'UTF-8');
    }
}

if (!function_exists('mb_trim')) {
    function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        return mb_internal_trim('{^[%s]+|[%1$s]+$}Du', $string, $characters, $encoding, __FUNCTION__);
    }
}

if (!function_exists('mb_ltrim')) {
    function mb_ltrim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        return mb_internal_trim('{^[%s]+}Du', $string, $characters, $encoding, __FUNCTION__);
    }
}

if (!function_exists('mb_rtrim')) {
    function mb_rtrim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        return mb_internal_trim('{[%s]+$}Du', $string, $characters, $encoding, __FUNCTION__);
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

if (!function_exists('mb_str_pad') && function_exists('mb_substr') && function_exists('mb_strlen')) {
    function mb_str_pad(string $string, int $length, string $pad_string = ' ', int $pad_type = \STR_PAD_RIGHT, ?string $encoding = null): string
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

if (!function_exists('stream_context_set_options')) {
    function stream_context_set_options($context, array $options): bool { return stream_context_set_option($context, $options); }
}

if (!function_exists('str_increment')) {
    function str_increment(string $string): string
    {
        if ('' === $string) {
            throw new \ValueError('str_increment(): Argument #1 ($string) cannot be empty');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $string)) {
            throw new \ValueError('str_increment(): Argument #1 ($string) must be composed only of alphanumeric ASCII characters');
        }

        if (is_numeric($string)) {
            $offset = stripos($string, 'e');
            if (false !== $offset) {
                $char = $string[$offset];
                ++$char;
                $string[$offset] = $char;
                ++$string;

                switch ($string[$offset]) {
                    case 'f':
                        $string[$offset] = 'e';
                        break;
                    case 'F':
                        $string[$offset] = 'E';
                        break;
                    case 'g':
                        $string[$offset] = 'f';
                        break;
                    case 'G':
                        $string[$offset] = 'F';
                        break;
                }

                return $string;
            }
        }

        return ++$string;
    }
}

if (!function_exists('str_decrement')) {
    function str_decrement(string $string): string
    {
        if ('' === $string) {
            throw new \ValueError('str_decrement(): Argument #1 ($string) cannot be empty');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $string)) {
            throw new \ValueError('str_decrement(): Argument #1 ($string) must be composed only of alphanumeric ASCII characters');
        }

        if (preg_match('/\A(?:0[aA0]?|[aA])\z/', $string)) {
            throw new \ValueError(sprintf('str_decrement(): Argument #1 ($string) "%s" is out of decrement range', $string));
        }

        if (!\in_array(substr($string, -1), ['A', 'a', '0'], true)) {
            return implode('', \array_slice(str_split($string), 0, -1)).\chr(\ord(substr($string, -1)) - 1);
        }

        $carry = '';
        $decremented = '';

        for ($i = \strlen($string) - 1; $i >= 0; --$i) {
            $char = $string[$i];

            switch ($char) {
                case 'A':
                    if ('' !== $carry) {
                        $decremented = $carry.$decremented;
                        $carry = '';
                    }
                    $carry = 'Z';

                    break;
                case 'a':
                    if ('' !== $carry) {
                        $decremented = $carry.$decremented;
                        $carry = '';
                    }
                    $carry = 'z';

                    break;
                case '0':
                    if ('' !== $carry) {
                        $decremented = $carry.$decremented;
                        $carry = '';
                    }
                    $carry = '9';

                    break;
                case '1':
                    if ('' !== $carry) {
                        $decremented = $carry.$decremented;
                        $carry = '';
                    }

                    break;
                default:
                    if ('' !== $carry) {
                        $decremented = $carry.$decremented;
                        $carry = '';
                    }

                    if (!\in_array($char, ['A', 'a', '0'], true)) {
                        $decremented = \chr(\ord($char) - 1).$decremented;
                    }
            }
        }

        return $decremented;
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
