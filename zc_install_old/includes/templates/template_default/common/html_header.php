<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: html_header.php 6981 2007-09-12 18:26:56Z drbyte $
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<?php
  if ($current_page=='database_upgrade' || $current_page == 'inspect') {
    if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) {
?>
<meta http-equiv="Expires" content="Mon, 26 Jul 2001 05:00:00 GMT" />
<meta http-equiv="Last-Modified" content= "<?php echo gmdate("D, d M Y H:i:s"); ?> GMT" />
<meta http-equiv="Cache-Control" content="must_revalidate, post-check=0, pre-check=0" />
<meta http-equiv="Pragma" content="public" />
<meta http-equiv="Cache-control" content="private" />
<?php
    } else {
?>
<meta http-equiv="Expires" content="Mon, 26 Jul 2001 05:00:00 GMT" />
<meta http-equiv="Last-Modified" content="<?php echo gmdate("D, d M Y H:i:s"); ?> GMT" />
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="CACHE-CONTROL" content="NO-CACHE" />
<?php
    }
  }
?>
<meta name="robots" content="noindex, nofollow" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<title><?php echo META_TAG_TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/templates/template_default/css/stylesheet.css" />
<script language="javascript" type="text/javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=450,height=280,screenX=150,screenY=150,top=150,left=150')
}
function popupWindowLrg(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,copyhistory=no,width=700,height=500,screenX=50,screenY=50,top=50,left=50')
}
//--></script>
</head>
