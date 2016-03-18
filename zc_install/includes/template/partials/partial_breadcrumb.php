<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 */

?>
<div>
  <ul class="breadcrumbs">
    <li <?php echo ($_GET['main_page'] == 'index') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_SYSTEM_INSPECTION; ?></a></li>
  <?php if ($_GET['main_page'] == 'database_upgrade' || (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')) { ?>
    <li <?php echo ($_GET['main_page'] == 'database_upgrade') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_DATABASE_UPGRADE; ?></a></li>
  <?php } else { ?>
    <li <?php echo ($_GET['main_page'] == 'system_setup') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_SYSTEM_SETUP; ?></a></li>
    <li <?php echo ($_GET['main_page'] == 'database') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_DATABASE_SETUP; ?></a></li>
    <li <?php echo ($_GET['main_page'] == 'admin_setup') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_ADMIN_SETUP; ?></a></li>
  <?php } ?>
    <li <?php echo ($_GET['main_page'] == 'completion') ? 'class="current"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_COMPLETION; ?></a></li>
  </ul>
</div>