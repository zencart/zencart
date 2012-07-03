<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2007 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: config_checkup_default.php 7414 2007-11-11 06:18:50Z drbyte $
 */

?>
  <div class="center">
    <img src="includes/templates/template_default/images/stop.gif" border="0" alt="There may be a problem with your configuration files." title="  There may be a problem with your configuration files. Please check the files and copy content from this page if necessary.  " />
  </div>
<?php echo TEXT_EXPLANATION2; ?>
<fieldset>
<legend><?php echo TEXT_CONFIG_FILES; ?></legend>
  <p><?php echo TEXT_CONFIG_INSTRUCTIONS; ?></p>
  <div id="cfgFiles">
  <form name="cfgFileForm">
  <div style="float:left"><a class="selectbox" href="javascript:selectfield('cfgCat')">Catalog</a><br /><?php echo TEXT_CATALOG_CONFIGFILE; ?><br />
  <textarea class="cfgfilecontent" rows="6" name="cfgCat" cols="35" wrap="virtual" onClick="this.select();" onFocus="this.select();"><?php echo $zc_install->configFiles['catalog']; ?></textarea>
  </div>
  <div style="float:left"><a class="selectbox" href="javascript:selectfield('cfgAdm')">Admin</a><br /><?php echo TEXT_ADMIN_CONFIGFILE; ?><br />
  <textarea class="cfgfilecontent" rows="6" name="cfgAdm" cols="35" wrap="virtual" onClick="this.select();" onFocus="this.select();"><?php echo $zc_install->configFiles['admin']; ?></textarea>
  </div>
  </form>
  </div>
</fieldset>
<form method="post" action="index.php?main_page=config_checkup&action=recheck<?php echo zcInstallAddSID(); ?>">
  <input type="submit" name="submit" class="button" value="<?php echo RECHECK; ?>" />
</form>
<form method="post" action="index.php?main_page=store_setup">
  <input type="submit" name="submit" class="button" value="<?php echo CONTINUE_BUTTON; ?>" />
<?php echo $zc_install->getConfigKeysAsPost(); ?>
</form>
<br /><br />
