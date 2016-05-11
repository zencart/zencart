<?php
/**
 * @package Installer
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: zcwilt  Wed Sep 23 20:04:38 2015 +0100 New in v1.5.5 $
 */
?>
<?php if ($isUpgrade) { ?>
<div class="alert-box success">
<?php echo TEXT_COMPLETION_UPGRADE_COMPLETE; ?>
</div>
<?php } else { ?>
<div class="alert-box success">
<?php echo TEXT_COMPLETION_INSTALL_COMPLETE; ?>
 <?php if ($catalogLink != '#') echo TEXT_COMPLETION_INSTALL_LINKS_BELOW; ?>
</div>
<?php } ?>
<?php if ($_POST['admin_directory'] == 'admin' && !defined('DEVELOPER_MODE')) { ?>
<br>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_ADMIN_DIRECTORY_WARNING; ?>
</div>
<br>
<?php } ?>
<?php if (file_exists(DIR_FS_INSTALL) && !defined('DEVELOPER_MODE')) { ?>
<br>
<div class="alert-box alert">
<?php echo TEXT_COMPLETION_INSTALLATION_DIRECTORY_WARNING; ?>
</div>
<br>
<?php } ?>
<?php if (!$isUpgrade && $catalogLink != '#') { ?>
<div>
<a class="radius button" href="<?php echo $adminLink; ?>" target="_blank" tabindex="1"><?php echo TEXT_COMPLETION_ADMIN_LINK_TEXT; ?>:<br><br><u><?php echo $adminLink; ?></u></a>
<a class="radius button" href="<?php echo $catalogLink; ?>" target="_blank" tabindex="2"><?php echo TEXT_COMPLETION_CATALOG_LINK_TEXT; ?>:<br><br><u><?php echo $catalogLink; ?></u></a>
</div>
<?php } ?>
