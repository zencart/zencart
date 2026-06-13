<?php
/**
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @license https://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */

/**
 * A helper function to retrieve a specific database constant
 * (from either the configuration or product_type_layout tables).
 *
 * The 2nd parameter ($default) identifies the value to be returned if
 * no match is found in either table. The default value of null enables
 * the use of the PHP null-coalesce operator on the returned value,
 *
 * e.g.
 * $value = zen_config('CONFIG_VALUE') ?? 'value not set';
 *
 * That's now equivalent to
 *
 * $value = zen_config('CONFIG_VALUE, 'value not set');
 *
 * @since ZC v3.0.0
 */
function zen_config(string $key, mixed $default = null): mixed
{
    global $configurationRepository, $productTypeLayoutRepository;

    if (!$configurationRepository) {
        return defined($key) ? constant($key) : $default;
    }

    return $configurationRepository->get($key) ?? $productTypeLayoutRepository->get($key) ?? $default;
}
