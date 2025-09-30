<?php
/**
 * polyfills to accommodate older/newer PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions (c) 2015-present Fabien Potencier <fabien@symfony.com>
 * @version $Id: DrByte 2025 August  Modified in v2.2.0-beta1 $
 * @since ZC v1.5.7c
 */

/* These polyfills are safe to load all the time, as they simply add PHP functions.
 * Since v1.5.7c you can use this file to replace /includes/functions/php_polyfills.php and it will support both admin and non-admin.
 *
 * Before v1.5.7c, you would need to copy this file into both of the following places to get the same support.
 * - /admin/includes/extra_configures/php_polyfills.php
 * - /includes/extra_configures/php_polyfills.php
 */

/* LICENSE
 *
 * Copyright (c) 2015-present Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if (\PHP_VERSION_ID >= 80500) {
    return;
}

if (!function_exists('get_error_handler')) {
    function get_error_handler(): ?callable
    {
        $handler = set_error_handler(null);
        restore_error_handler();

        return $handler;
    }
}

if (!function_exists('get_exception_handler')) {
    function get_exception_handler(): ?callable
    {
        $handler = set_exception_handler(null);
        restore_exception_handler();

        return $handler;
    }
}

if (!function_exists('array_first')) {
    function array_first(array $array)
    {
        foreach ($array as $value) {
            return $value;
        }

        return null;
    }
}

if (!function_exists('array_last')) {
    function array_last(array $array)
    {
        return $array ? current(array_slice($array, -1)) : null;
    }
}


if (\PHP_VERSION_ID >= 80400) {
    return;
}

if ((defined('CURL_VERSION_HTTP3') || PHP_VERSION_ID < 80200) && function_exists('curl_version') && curl_version()['version'] >= 0x074200) { // libcurl >= 7.66.0
    if (!defined('CURL_HTTP_VERSION_3')) {
        define('CURL_HTTP_VERSION_3', 30);
    }

    if (!defined('CURL_HTTP_VERSION_3ONLY') && defined('CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256')) { // libcurl >= 7.80.0 (7.88 would be better but is slow to check)
        define('CURL_HTTP_VERSION_3ONLY', 31);
    }
}

if (extension_loaded('bcmath')) {
    if (!function_exists('bcdivmod')) {
        function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array
        {
            if (null === $quot = \bcdiv($num1, $num2, 0)) {
                return null;
            }
            $scale = $scale ?? (\PHP_VERSION_ID >= 70300 ? \bcscale() : (ini_get('bcmath.scale') ?: 0));

            return [$quot, \bcmod($num1, $num2, $scale)];
        }
    }
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

if (!function_exists('fpow')) {
    function fpow(float $num, float $exponent): float
    {
        return $num ** $exponent;
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
            $characters = preg_quote($characters, null);
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
if (!class_exists('DateError')) {
    class DateError extends Error {}
}
if (!class_exists('DateObjectError')) {
    class DateObjectError extends DateError {}
}
if (!class_exists('DateRangeError')) {
    class DateRangeError extends DateError {}
}
if (!class_exists('DateException')) {
    class DateException extends Exception {}
}
if (!class_exists('DateInvalidOperationException')) {
    class DateInvalidOperationException extends DateException {}
}
if (!class_exists('DateInvalidTimeZoneException')) {
    class DateInvalidTimeZoneException extends DateException {}
}
if (!class_exists('DateMalformedIntervalStringException')) {
    class DateMalformedIntervalStringException extends DateException {}
}
if (!class_exists('DateMalformedPeriodStringException')) {
    class DateMalformedPeriodStringException extends DateException {}
}
if (!class_exists('DateMalformedStringException')) {
    class DateMalformedStringException extends DateException {}
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
if (!class_exists('TypeError')) {
    class TypeError extends Error {}
}
if (!class_exists('UnhandledMatchError')) {
    class UnhandledMatchError extends Error {}
}

if (!class_exists('ValueError')) {
    class ValueError extends Error {}
}
interface Stringable
{
    /**
     * @return string
     */
    public function __toString();
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
        if ('' === $needle || $needle === $haystack) {
            return true;
        }

        if ('' === $haystack) {
            return false;
        }

        $needleLength = \strlen($needle);

        return $needleLength <= \strlen($haystack) && 0 === substr_compare($haystack, $needle, -$needleLength);
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



if (\PHP_VERSION_ID >= 70400) {
    return;
}

if (!function_exists('get_mangled_object_vars')) {
    function get_mangled_object_vars($obj)
    {
        if (!\is_object($obj)) {
            trigger_error('get_mangled_object_vars() expects parameter 1 to be object, '.\gettype($obj).' given', \E_USER_WARNING);

            return null;
        }

        if ($obj instanceof \ArrayIterator || $obj instanceof \ArrayObject) {
            $reflector = new \ReflectionClass($obj instanceof \ArrayIterator ? 'ArrayIterator' : 'ArrayObject');
            $flags = $reflector->getMethod('getFlags')->invoke($obj);
            $reflector = $reflector->getMethod('setFlags');

            $reflector->invoke($obj, ($flags & \ArrayObject::STD_PROP_LIST) ? 0 : \ArrayObject::STD_PROP_LIST);
            $arr = (array) $obj;
            $reflector->invoke($obj, $flags);
        } else {
            $arr = (array) $obj;
        }

        return array_combine(array_keys($arr), array_values($arr));
    }
}
if (!function_exists('password_algos')) {
    function password_algos()
    {
        $algos = [];

        if (\defined('PASSWORD_BCRYPT')) {
            $algos[] = \PASSWORD_BCRYPT;
        }

        if (\defined('PASSWORD_ARGON2I')) {
            $algos[] = \PASSWORD_ARGON2I;
        }

        if (\defined('PASSWORD_ARGON2ID')) {
            $algos[] = \PASSWORD_ARGON2ID;
        }

        return $algos;
    }
}
if (extension_loaded('mbstring')) {
    if (!function_exists('mb_str_split')) {
        function mb_str_split($string, $split_length = 1, $encoding = null)
        {
            if (null !== $string && !\is_scalar($string) && !(\is_object($string) && method_exists($string, '__toString'))) {
                trigger_error('mb_str_split() expects parameter 1 to be string, '.\gettype($string).' given', \E_USER_WARNING);

                return null;
            }

            if (1 > $split_length = (int) $split_length) {
                trigger_error('The length of each segment must be greater than zero', \E_USER_WARNING);

                return false;
            }

            if (null === $encoding) {
                $encoding = mb_internal_encoding();
            }

            if ('UTF-8' === $encoding || \in_array(strtoupper($encoding), ['UTF-8', 'UTF8'], true)) {
                return preg_split("/(.{{$split_length}})/u", $string, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);
            }

            $result = [];
            $length = mb_strlen($string, $encoding);

            for ($i = 0; $i < $length; $i += $split_length) {
                $result[] = mb_substr($string, $i, $split_length, $encoding);
            }

            return $result;
        }
    }
}

if (\PHP_VERSION_ID >= 70300) {
    return;
}
if (!class_exists('JsonException')) {
    class JsonException extends Exception {}
}
if (!function_exists('is_countable')) {
    function is_countable($value) { return is_array($value) || $value instanceof Countable || $value instanceof ResourceBundle || $value instanceof SimpleXmlElement; }
}
if (!function_exists('hrtime') && !class_exists('Php73')) {
    class Php73
    {
        public static $startAt = 1533462603;

        /**
         * @param bool $asNum
         *
         * @return array|float|int
         */
        public static function hrtime($asNum = false)
        {
            $ns = microtime(false);
            $s = substr($ns, 11) - self::$startAt;
            $ns = 1E9 * (float) $ns;

            if ($asNum) {
                $ns += $s * 1E9;

                return \PHP_INT_SIZE === 4 ? $ns : (int) $ns;
            }

            return [$s, (int) $ns];
        }
    }
    Php73::$startAt = (int) microtime(true);
    function hrtime($as_number = false) { return Php73::hrtime($as_number); }
}
if (!function_exists('array_key_first')) {
    function array_key_first(array $array) { foreach ($array as $key => $value) { return $key; } }
}
if (!function_exists('array_key_last')) {
    function array_key_last(array $array) { return key(array_slice($array, -1, 1, true)); }
}



if (\PHP_VERSION_ID >= 70200) {
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
if (!function_exists('php_os_family')) {
    function php_os_family()
    {
        if ('\\' === \DIRECTORY_SEPARATOR) {
            return 'Windows';
        }

        $map = [
            'Darwin' => 'Darwin',
            'DragonFly' => 'BSD',
            'FreeBSD' => 'BSD',
            'NetBSD' => 'BSD',
            'OpenBSD' => 'BSD',
            'Linux' => 'Linux',
            'SunOS' => 'Solaris',
        ];

        return $map[\PHP_OS] ?? 'Unknown';
    }
}
if (!defined('PHP_OS_FAMILY')) {
    define('PHP_OS_FAMILY', php_os_family());
}

if (extension_loaded('mbstring')) {
    if (!function_exists('mb_ord')) {
        function mb_ord($s, $encoding = null)
        {
            if (null === $encoding) {
                $s = mb_convert_encoding($s, 'UTF-8');
            } elseif ('UTF-8' !== $encoding) {
                $s = mb_convert_encoding($s, 'UTF-8', $encoding);
            }

            if (1 === \strlen($s)) {
                return \ord($s);
            }

            $code = ($s = unpack('C*', substr($s, 0, 4))) ? $s[1] : 0;
            if (0xF0 <= $code) {
                return (($code - 0xF0) << 18) + (($s[2] - 0x80) << 12) + (($s[3] - 0x80) << 6) + $s[4] - 0x80;
            }
            if (0xE0 <= $code) {
                return (($code - 0xE0) << 12) + (($s[2] - 0x80) << 6) + $s[3] - 0x80;
            }
            if (0xC0 <= $code) {
                return (($code - 0xC0) << 6) + $s[2] - 0x80;
            }

            return $code;
        }
    }
    if (!function_exists('mb_chr')) {
        function mb_chr($code, $encoding = null)
        {
            if (0x80 > $code %= 0x200000) {
                $s = \chr($code);
            } elseif (0x800 > $code) {
                $s = \chr(0xC0 | $code >> 6).\chr(0x80 | $code & 0x3F);
            } elseif (0x10000 > $code) {
                $s = \chr(0xE0 | $code >> 12).\chr(0x80 | $code >> 6 & 0x3F).\chr(0x80 | $code & 0x3F);
            } else {
                $s = \chr(0xF0 | $code >> 18).\chr(0x80 | $code >> 12 & 0x3F).\chr(0x80 | $code >> 6 & 0x3F).\chr(0x80 | $code & 0x3F);
            }

            if ('UTF-8' !== $encoding = $encoding ?? mb_internal_encoding()) {
                $s = mb_convert_encoding($s, $encoding, 'UTF-8');
            }

            return $s;
        }
    }
    if (!function_exists('mb_scrub')) {
        function mb_scrub($string, $encoding = null) { $encoding = null === $encoding ? mb_internal_encoding() : $encoding; return mb_convert_encoding($string, $encoding, $encoding); }
    }
}



if (PHP_VERSION_ID >= 70100) {
    // return; // bypassed 'return' in Symfony's polyfill, so bypassing here too.
}
if (!function_exists('is_iterable')) {
    function is_iterable($var)
    {
        return \is_array($var) || $var instanceof \Traversable;
    }
}



if (PHP_VERSION_ID >= 70000) {
    return;
}
if (!class_exists('Error')) {
    class Error extends Exception {}
}
if (!class_exists('ArithmeticError')) {
    class ArithmeticError extends Error {}
}
if (!class_exists('AssertionError')) {
    class AssertionError extends Error {}
}
if (!class_exists('DivisionByZeroError')) {
    class DivisionByZeroError extends ArithmeticError {}
}
if (!class_exists('ParseError')) {
    class ParseError extends Error {}
}
if (!class_exists('TypeError')) {
    class TypeError extends Error {}
}

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', ~PHP_INT_MAX);
}

if (!function_exists('intArg')) {
    // This is a helper function, not to be called directly
    function intArg($value, $caller, $pos)
    {
        if (\is_int($value)) {
            return $value;
        }
        if (!\is_numeric($value) || PHP_INT_MAX <= ($value += 0) || ~PHP_INT_MAX >= $value) {
            throw new \InvalidArgumentException(sprintf('%s() expects parameter %d to be integer, %s given', $caller, $pos, \gettype($value)));
        }

        return (int)$value;
    }
}
if (!function_exists('intdiv')) {
    function intArg($value, $caller, $pos)
    {
        if (\is_int($value)) {
            return $value;
        }
        if (!\is_numeric($value) || PHP_INT_MAX <= ($value += 0) || ~PHP_INT_MAX >= $value) {
            throw new \TypeError(sprintf('%s() expects parameter %d to be integer, %s given', $caller, $pos, \gettype($value)));
        }

        return (int) $value;
    }
    function intdiv($dividend, $divisor)
    {
        $dividend = intArg($dividend, __FUNCTION__, 1);
        $divisor = intArg($divisor, __FUNCTION__, 2);

        if (0 === $divisor) {
            throw new \DivisionByZeroError('Division by zero');
        }
        if (-1 === $divisor && ~PHP_INT_MAX === $dividend) {
            throw new \ArithmeticError('Division of PHP_INT_MIN by -1 is not an integer');
        }

        return ($dividend - ($dividend % $divisor)) / $divisor;
    }
}
if (!function_exists('preg_replace_callback_array')) {
    function preg_replace_callback_array(array $patterns, $subject, $limit = -1, &$count = 0, $flags = null)
    {
        $count = 0;
        $result = (string) $subject;
        if (0 === $limit = intArg($limit, __FUNCTION__, 3)) {
            return $result;
        }

        foreach ($patterns as $pattern => $callback) {
            $result = preg_replace_callback($pattern, $callback, $result, $limit, $c);
            $count += $c;
        }

        return $result;
    }
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
if (!function_exists('random_bytes')) {
    function random_bytes($length)
    {
        $length = intArg($length, __FUNCTION__, 1);
        if ($length < 1) {
            throw new \TypeError(sprintf('%s(): Length must be greater than 0', __FUNCTION__));
        }

        $bytes = openssl_random_pseudo_bytes($length, $strong);
        if (false === $bytes || !$strong || \strlen($bytes) < $length) {
            throw new \Exception(sprintf('%s(): Unable to generate %d random bytes', __FUNCTION__, $length));
        }

        return $bytes;
    }
}
if (!function_exists('random_int')) {
    function random_int($min, $max)
    {
        $min = intArg($min, __FUNCTION__, 1);
        $max = intArg($max, __FUNCTION__, 2);
        if ($min > $max) {
            throw new \TypeError(sprintf('%s(): Minimum value must be less than or equal to the maximum value', __FUNCTION__));
        }
        if ($min === $max) {
            return $min;
        }
        $range = $max - $min;
        $bytes = (int) \ceil(\log($range + 1, 2) / 8);
        $bits = (int) \ceil(\log($range + 1, 2));
        $filter = (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(random_bytes($bytes))) & $filter;
        } while ($rnd > $range);
        return $min + $rnd;
    }
}
