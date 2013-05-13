<?php
/**
 * ajaxGetHelpText.php
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
define('IS_ADMIN_FLAG', false);
define('DIR_FS_INSTALL', realpath(dirname(__FILE__) . '/') . '/');
define('DIR_FS_ROOT', realpath(dirname(__FILE__) . '/../') . '/');

require(DIR_FS_INSTALL . 'includes/application_top.php');

if (isset($_POST['id']))
{
  $result = str_replace('helpId', '' , zen_output_string_protected($_POST['id']));
  $content = "TEXT_HELP_CONTENT_" . strtoupper($result);
  $content = "<p>".constant($content) . "</p>";
  $title = "TEXT_HELP_TITLE_" . strtoupper($result);
  $title = constant($title);
}

echo json_encode(array('text'=>$content, 'title'=>$title));
