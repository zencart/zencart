<?php
/**
 * An early-loading function (loaded by /includes/application_top.php and /admin/includes/application_bootstrap.php)
 * to simplify the processing to set a default value for a 'define' if the definition is not yet present.
 *
 * @version $Id: DrByte 2025 Sep 18 Modified in v2.2.0 $
 * @since ZC v1.5.8
 */
function zen_define_default(string $name, $default_value)
{
    if (!defined($name)) {
        define($name, $default_value);
    }
}
