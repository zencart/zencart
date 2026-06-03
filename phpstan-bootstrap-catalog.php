<?php

declare(strict_types=1);

$root = __DIR__ . DIRECTORY_SEPARATOR;

// Mirror storefront entrypoint behavior for relative include/file_exists checks.
chdir($root);
set_include_path($root . PATH_SEPARATOR . $root . 'includes' . PATH_SEPARATOR . get_include_path());

// Skip loading includes/configure.php during static analysis.
defined('ZENCART_TESTFRAMEWORK_RUNNING') || define('ZENCART_TESTFRAMEWORK_RUNNING', true);

defined('DIR_FS_CATALOG') || define('DIR_FS_CATALOG', $root);
defined('DIR_WS_INCLUDES') || define('DIR_WS_INCLUDES', 'includes/');
defined('DIR_WS_FUNCTIONS') || define('DIR_WS_FUNCTIONS', 'includes/functions/');
defined('DIR_WS_CLASSES') || define('DIR_WS_CLASSES', 'includes/classes/');
defined('DIR_FS_INCLUDES') || define('DIR_FS_INCLUDES', $root . 'includes/');

