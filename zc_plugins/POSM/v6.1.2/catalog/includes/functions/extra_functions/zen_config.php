<?php
/**
 * zen_config.php: Provides a zc300 function for use by earlier ZC versions,
 *
 */
if (!function_exists('zen_config')) {
    /**
     * Function to retrieve a database constant, presumed to be in either
     * the configuration or product_type_layout tables.
     */
    function zen_config(string $key, mixed $default = null): mixed
    {
        return defined($key) ? constant($key) : $default;
    }
}
