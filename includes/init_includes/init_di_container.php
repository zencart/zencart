<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 30/04/16
 * Time: 09:57
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
