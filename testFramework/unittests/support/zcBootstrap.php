<?php
/**
 * Unit testing common setup actions
 */
$bypassWarning = TRUE; // bypass PHPUnit/Framework warning error (works on edited localhost code ... will have to customize Bamboo to do the same if the next line can't be removed
if (file_exists('PHPUnit/Framework.php') && ! file_exists('PHPUnit/Autoload.php'))
  require_once 'PHPUnit/Framework.php';

require_once('zcTestCase.php');
require_once('zcAdminTestCase.php');
require_once('zcCatalogTestCase.php');
require_once('zcUrlGenerationTestCase.php');
