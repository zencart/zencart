<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */

?>
<div>
  <ul class="crumb">
    <li <?php echo ($_GET['main_page'] == 'index') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_SYSTEM_INSPECTION; ?></a></li>
  <?php if ($_GET['main_page'] == 'database_upgrade' || (isset($_POST['upgrade_mode']) && $_POST['upgrade_mode'] == 'yes')) { ?>
    <li <?php echo ($_GET['main_page'] == 'database_upgrade') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_DATABASE_UPGRADE; ?></a></li>
  <?php } else { ?>  
    <li <?php echo ($_GET['main_page'] == 'system_setup') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_SYSTEM_SETUP; ?></a></li>
    <li <?php echo ($_GET['main_page'] == 'database') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_DATABASE_SETUP; ?></a></li>
    <li <?php echo ($_GET['main_page'] == 'admin_setup') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_ADMIN_SETUP; ?></a></li>
  <?php } ?>  
    <li <?php echo ($_GET['main_page'] == 'completion') ? 'class="active"' : ""; ?>><a href="#"><?php echo TEXT_NAVBAR_COMPLETION; ?></a></li>
  </ul>
</div>  