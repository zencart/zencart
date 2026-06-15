<?php
/**
 * polyfills to accommodate older/newer PHP versions, adapted from https://github.com/symfony/polyfill/
 * @copyright Portions (c) 2015-present Fabien Potencier <fabien@symfony.com>
 * @version $Id: DrByte 2026 Mar 10 Modified in v3.0 $
 * @since ZC v1.5.7c
 *
 * Note: This set of polyfills is related to the supported PHP versions for Zen Cart.
 * It's better to use an officially-supported PHP version, but these provide some compatibility of function differences across PHP versions.
 */

/**
 * MbString polyfill
 */
require DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/polyfill-mbstring/Mbstring.php';
require DIR_FS_CATALOG . DIR_WS_CLASSES . 'vendors/polyfill-mbstring/bootstrap80.php';

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

if (\extension_loaded('curl')) {
    if (!defined('CURL_HTTP_VERSION_3')) {
        define('CURL_HTTP_VERSION_3', 30);
    }

    // CURL_HTTP_VERSION_3ONLY requires libcurl >= 7.88.0 and is not gated by any PHP-defined constant before 8.4
    if (!defined('CURL_HTTP_VERSION_3ONLY') && curl_version()['version_number'] >= 0x075800) {
        define('CURL_HTTP_VERSION_3ONLY', 31);
    }
}

if (extension_loaded('bcmath')) {
    if (!function_exists('bcdivmod')) {
        function bcdivmod(string $num1, string $num2, ?int $scale = null): ?array
        {
            if (null === $quot = @bcdiv($num1, $num2, 0)) {
                throw new \DivisionByZeroError('Division by zero');
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
