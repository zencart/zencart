<?php
/**
 * @package admin
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: index.php 19537 2011-09-20 17:14:44Z drbyte $
 */
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<?php if (!defined('SKIP_CORE_JQUERY_LOADER') || SKIP_CORE_JQUERY_LOADER == FALSE) { ?>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
<?php   
  //fall back to local if CDN copy didn't load or if offline
  $jqpath = DIR_WS_ADMIN . 'includes/template/javascript/jquery.min.js';
  $jquipath = DIR_WS_ADMIN . 'includes/template/javascript/jquery-ui.min.js';
  $jquicsspath = DIR_WS_ADMIN . 'includes/template/javascript/jquery-ui.min.css';
  if (file_exists(str_replace(DIR_WS_ADMIN, DIR_FS_ADMIN, $jqpath))) { ?>
    <script>window.jQuery || document.write('<script src="<?php echo $jqpath;?>"><\/script><script src="<?php echo $jquipath;?>"><\/script><link type="stylesheet" href="<?php echo $jquicsspath;?>" />');</script>
<?php } ?>
<?php } ?>
<link rel="stylesheet" type="text/css" href="includes/template/css/foundation.css">
<link rel="stylesheet" type="text/css" href="includes/template/css/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/template/css/menu.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<?php if (isset($extraCss)) { ?>
  <?php foreach ($extraCss as $css) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $css['location']; ?>">
  <?php } ?>  
<?php } ?>
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script language="javascript" src="includes/template/javascript/foundation/jquery.foundation.reveal.js"></script>
<?php require "includes/template/javascript/zcJSFramework.js.php"; ?>
<script type="text/javascript">
  <!--
  $(document).ready(function(){
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  });
  // -->
</script>
