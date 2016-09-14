<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
use Aura\Di\ContainerBuilder;
use Aura\Web\Request as WebRequest;

$diConfigFiles = array();
if ($dirContents = @dir(DIR_WS_INCLUDES . 'diConfigs')) {
    while ($dirFile = $dirContents->read()) {
        if (preg_match('~^[^\._].*\.php$~i', $dirFile) > 0) {
            require(DIR_WS_INCLUDES . 'diConfigs/' . $dirFile);
            $className = pathinfo($dirFile, PATHINFO_FILENAME);
            $config = new $className();
            $diConfigFiles[] = $config;
        }
    }
    $dirContents->close();
}

$builder = new ContainerBuilder();
$di = $builder->newConfiguredInstance($diConfigFiles);
$zcRequest = $di->get('zencart_request');
