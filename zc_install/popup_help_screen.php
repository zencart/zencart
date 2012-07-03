<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: popup_help_screen.php 2342 2005-11-13 01:07:55Z drbyte $
 */


  require('includes/application_top.php');
  require('includes/languages/' . $language . '.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" /> 
<title><?php echo META_TAG_TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/templates/template_default/css/stylesheet.css">
</head>
<body id="popup"></body>
<div id="popup_header">
<h1>
<?php
  echo POPUP_ERROR_HEADING;
  echo '<br /><br />';
?>
</h1>
</div>
<div id="popup_content">
<?php
  echo POPUP_ERROR_TEXT;
  echo '<br /><br />';
?>
</div>
<?php
  echo '<center>' . '<a href="javascript:window.close()">' . TEXT_CLOSE_WINDOW . '</a></center>';
?>
</body>
</html>