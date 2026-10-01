<?php
/**
 * Compatibility bridge: zen_config() was introduced in Zen Cart v3.0.0.
 * This plugin is also installable on v2.2.0+, where the function does not exist,
 * so provide a minimal fallback that reads the already-defined configuration constant.
 *
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */
if (!function_exists('zen_config')) {
    /**
     * Retrieve a configuration value (normally loaded as a constant from the
     * configuration or product_type_layout tables), or $default if not defined.
     */
    function zen_config(string $key, mixed $default = null): mixed
    {
        return defined($key) ? constant($key) : $default;
    }
}
