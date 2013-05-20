<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2013 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: 
 */
?>
<?php if ($isUpgrade) { ?>
<div class="alert-box success">
<?php echo TEXT_COMPLETION_UPGRADE_COMPLETE; ?>
</div>
<?php } else { ?>
<div class="alert-box success">
<?php echo TEXT_COMPLETION_INSTALL_COMPLETE; ?>
</div>
<?php } ?>
<?php if ($_POST['admin_directory'] == 'admin') { ?>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_ADMIN_DIRECTORY_WARNING; ?>
</div>
<?php } ?>
<?php if (file_exists(DIR_FS_INSTALL)) { ?>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_INSTALLATION_DIRECTORY_WARNING; ?> 
</div>
<?php } ?>
<?php if (!$isUpgrade) { ?>
<div>
<a class="radius button" href="<?php echo $adminLink; ?>" target="_blank">Your Store Admin</a>
<a class="radius button right" href="<?php echo $catalogLink; ?>" target="_blank">Your Store Front</a>
</div>
<?php } ?>
