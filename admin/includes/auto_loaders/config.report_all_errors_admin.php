<?php
/**
 * Autoloader to instantiate initialization, just after the database configuration constants have been initialized
 */
$autoLoadConfig[21][] = array('autoType'=>'init_script',
                              'loadFile'=>'init_report_all_errors_admin.php');