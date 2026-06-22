<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!function_exists('zen_href_link')) {
    function zen_href_link(string $filename, string $parameters = ''): string
    {
        return $filename . ($parameters === '' ? '' : '?' . $parameters);
    }
}
