<?php

if (!defined('IS_ADMIN_FLAG')) {
    define('IS_ADMIN_FLAG', false);
}

if (!function_exists('zen_href_link')) {
    function zen_href_link($page = '', $parameters = '', $connection = 'NONSSL'): string
    {
        return '/index.php?main_page=' . $page;
    }
}

require DIR_FS_CATALOG . 'includes/templates/template_default/jscript/jscript_framework.php';
