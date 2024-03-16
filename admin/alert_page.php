<?php
/**
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott Wilson 2024 Mar 10 Modified in v2.0.0-rc2 $
 */
require('includes/application_top.php');
$adminDirectoryExists = $installDirectoryExists = false;
if (substr(DIR_WS_ADMIN, -7) == '/admin/' || substr(DIR_WS_HTTPS_ADMIN, -7) == '/admin/') {
    $adminDirectoryExists = true;
}
$check_path = dirname($_SERVER['SCRIPT_FILENAME']) . '/../zc_install';
if (is_dir($check_path)) {
    $installDirectoryExists = true;
}
if (!$adminDirectoryExists && !$installDirectoryExists) {
    zen_redirect(zen_href_link(FILENAME_DEFAULT));
}
?>
<!doctype html>
<html dir="ltr" lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zen Cart!</title>
    <meta name="robots" content="noindex, nofollow">
  </head>
  <body style="font-family:Helvetica, Arial, sans-serif">
    <div style="width:80%;margin: 10% auto auto;border:5px red solid;padding:20px 50px 50px 50px;background-color:#FFAFAF;">
      <h1 style="text-align: center;font-size:40px;color:red;margin:0;"><?php echo HEADING_TITLE; ?></h1>
      <p><?php echo ALERT_PART1; ?></p>
      <ul>
        <?php if ($installDirectoryExists) { ?>
          <li><?php echo ALERT_REMOVE_ZCINSTALL; ?><br><br></li>
        <?php } ?>
        <?php if ($adminDirectoryExists) { ?>
          <li><?php echo ALERT_RENAME_ADMIN; ?><br><a href="https://docs.zen-cart.com/user/installing/rename_admin/" rel="noopener" target="_blank"><?php echo ADMIN_RENAME_FAQ_NOTE; ?></a></li>
        <?php } ?>
      </ul>
      <?php if ($adminDirectoryExists) { ?>
        <br><p><?php echo ALERT_PART2; ?></p>
      <?php } ?>
    </div>

  </body>
</html>
