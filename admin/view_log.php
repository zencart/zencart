<<<<<<< HEAD
<?php
/**
 * @package admin
 * @copyright Copyright 2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

  require('includes/application_top.php');
  require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<div class="header-area">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->

<!-- body //-->
<div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
<br />
<?php 
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
   $pathname = DIR_FS_LOGS . "/";
   $filename = $_GET['logname']; 
   $file = $pathname . $filename;
   $rpdir = realpath($file);
   $rppathdir = realpath($pathname);
   if ( (substr($rpdir, 0, strlen($rppathdir)) != $rppathdir) || 
        (strlen($rpdir) < strlen($rppathdir)) || 
        (!file_exists($file)) ) {
      echo FILE_NOT_FOUND; 
   } else {
      echo '<strong>' . PATH . '</strong>' .  $pathname . '<br />'; 
      echo '<strong>' . FILENAME . '</strong>' .  $filename . '<br /><br />'; 
      $file_array = @file($file);
      $file_contents = @implode('', $file_array);
      echo nl2br(zen_output_string_protected($file_contents)); 
   }

?>
<!-- body_eof //-->

<!-- footer //-->
<div class="footer-area">
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
=======
<?php
/**
 * @package admin
 * @copyright Copyright 2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

  require('includes/application_top.php');
  require('includes/admin_html_head.php');
?>
</head>
<body>
<!-- header //-->
<div class="header-area">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->

<!-- body //-->
<div class="pageHeading"><?php echo HEADING_TITLE; ?></div>
<br />
<?php 
  if (!defined('DIR_FS_LOGS')) define('DIR_FS_LOGS', DIR_FS_CATALOG . 'logs');
   $pathname = DIR_FS_LOGS . "/";
   $filename = $_GET['logname']; 
   $file = $pathname . $filename;
   $rpdir = realpath($file);
   $rppathdir = realpath($pathname);
   if ( (substr($rpdir, 0, strlen($rppathdir)) != $rppathdir) || 
        (strlen($rpdir) < strlen($rppathdir)) || 
        (!file_exists($file)) ) {
      echo FILE_NOT_FOUND; 
   } else {
      echo '<strong>' . PATH . '</strong>' .  $pathname . '<br />'; 
      echo '<strong>' . FILENAME . '</strong>' .  $filename . '<br /><br />'; 
      $file_array = @file($file);
      $file_contents = @implode('', $file_array);
      echo nl2br(zen_output_string_protected($file_contents)); 
   }

?>
<!-- body_eof //-->

<!-- footer //-->
<div class="footer-area">
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
>>>>>>> refs/remotes/zencart/v160
