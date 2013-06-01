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
<br>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_ADMIN_DIRECTORY_WARNING; ?>
</div>
<br>
<?php } ?>
<?php if (file_exists(DIR_FS_INSTALL)) { ?>
<br>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_INSTALLATION_DIRECTORY_WARNING; ?>
</div>
<br>
<?php } ?>
<?php if (!$isUpgrade) { ?>
<div>
<a class="radius button" href="<?php echo $adminLink; ?>" target="_blank"><?php echo TEXT_COMPLETION_ADMIN_LINK_TEXT; ?><br><u><?php echo $adminLink; ?></u></a>
<a class="radius button" href="<?php echo $catalogLink; ?>" target="_blank"><?php echo TEXT_COMPLETION_CATALOG_LINK_TEXT; ?><br><u><?php echo $catalogLink; ?></u></a>
</div>
<?php } ?>
