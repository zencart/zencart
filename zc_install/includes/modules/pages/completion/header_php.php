<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

require (DIR_FS_INSTALL . 'includes/classes/class.zcDatabaseInstaller.php');

if (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')
{
	$isUpgrade = TRUE;
  $systemChecker = new systemChecker();
	$options = $systemChecker->getDbConfigOptions();
  $dbInstaller = new zcDatabaseInstaller($options);
	$db = $dbInstaller->getDb();
	if (isset($_POST['admin_candidate']) && $_POST['admin_candidate'] != '')
	{
		$sql = "UPDATE " . $options['db_prefix'] . "admin set admin_profile = 1 WHERE admin_id = :adminCandidate:";
		$sql = $db->bindVars($sql, ':adminCandidate:', $_POST['admin_candidate'], 'integer');
		$result = $db->execute($sql);
	}
} else 
{
	$isUpgrade = FALSE;
  require (DIR_FS_INSTALL . 'includes/classes/class.zcConfigureFileWriter.php');
  $result = new zcConfigureFileWriter($_POST);
  $options = $_POST;
  $dbInstaller = new zcDatabaseInstaller($options);
  $result = $dbInstaller->getConnection();
  $extendedOptions = array();
  $error = $dbInstaller->doCompletion($options);
  $adminLink = zen_output_string_protected($_POST['http_server_admin']) . zen_output_string_protected($_POST['dir_ws_http_catalog']) . zen_output_string_protected($_POST['admin_directory']);
  $catalogLink = zen_output_string_protected($_POST['http_server_catalog']) . zen_output_string_protected($_POST['dir_ws_http_catalog']);
}
