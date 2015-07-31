<?php
/**
 * @package tests
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
/**
 * Loads a connection class. By default this is the local selenium driver
 * however we can also set the CONNECTOR_TYPE in the localconfig_ file
 * the only other connector type is for sauce labs e.g. define('CONNECTOR_TYPE', 'sauce')
 */
$connectorType = 'selenium';
if (defined('CONNECTOR_TYPE')) {
    $connectorType = CONNECTOR_TYPE;
}
$connectorFile = 'testFramework/webtests/support/connector' . ucfirst($connectorType) . '.php';
if (file_exists($connectorFile)) {
  require_once($connectorFile);
}
