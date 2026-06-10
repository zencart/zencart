<?php

declare(strict_types=1);

$root = __DIR__ . DIRECTORY_SEPARATOR;

// Mirror admin entrypoint behavior for relative include/file_exists checks.
chdir($root . 'admin');
set_include_path(
	$root . 'admin' . PATH_SEPARATOR . $root . 'admin/includes' . PATH_SEPARATOR . $root . PATH_SEPARATOR . get_include_path()
);

// Skip loading includes/configure.php during static analysis.
defined('ZENCART_TESTFRAMEWORK_RUNNING') || define('ZENCART_TESTFRAMEWORK_RUNNING', true);

defined('DIR_FS_CATALOG') || define('DIR_FS_CATALOG', $root);
defined('DIR_WS_INCLUDES') || define('DIR_WS_INCLUDES', 'includes/');
defined('DIR_WS_FUNCTIONS') || define('DIR_WS_FUNCTIONS', 'includes/functions/');
defined('DIR_WS_CLASSES') || define('DIR_WS_CLASSES', 'includes/classes/');
defined('DIR_FS_ADMIN') || define('DIR_FS_ADMIN', $root . 'admin/');
defined('DIR_FS_ADMIN_INCLUDES') || define('DIR_FS_ADMIN_INCLUDES', $root . 'admin/includes/');

