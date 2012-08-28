<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2012 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: database_upgrade_default.php 18695 2011-05-04 05:24:19Z drbyte $
 */

  if ($zc_install->error) include(DIR_WS_INSTALL_TEMPLATE . 'templates/display_errors.php');
?>
    <form method="post" action="index.php?main_page=database_upgrade<?php echo zcInstallAddSID(); ?>">
<?php if ($dbinfo->zdb_configuration_table_found) { ?>
<p><?php echo TEXT_MAIN_2; ?></p>
    <fieldset>
    <legend><?php echo DATABASE_INFORMATION . ' -- &nbsp;<strong>' . SNIFFER_PREDICTS . ' ' . $sniffer_text . '</strong>'; ?></legend>
      <div class="section">
        <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=14\')"> ' . DATABASE_TYPE. '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . DB_TYPE; ?>
    </div>
    <div class="section">
      <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=15\')"> ' . DATABASE_HOST . '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . DB_SERVER; ?>
    </div>
    <div class="section">
      <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=18\')"> ' . DATABASE_NAME . '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . DB_DATABASE; ?>
    </div>
    <div class="section">
      <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=16\')"> ' . DATABASE_USERNAME . '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . DB_SERVER_USERNAME; ?>
    </div>
    <div class="section">
      <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=19\')"> ' . DATABASE_PREFIX . '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . DB_PREFIX; ?>
    </div>
<!--    <div class="section">
      <label><?php echo '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=87\')"> ' . DATABASE_PRIVILEGES . '</a>'; ?></label>
      <?php echo '&nbsp;=&nbsp;' . $zdb_privs; ?>
    </div>-->
    </fieldset>
    <br />

    <fieldset>
    <legend><strong><?php echo CHOOSE_UPGRADES; ?></strong></legend>
    <div class="input">
      <input <?php if ($needs_v1_3_0) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox14" value="1.2.7" tabindex="14" />
      <label for="checkbox14">Upgrade DB from 1.2.7 to 1.3.0</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_0_1) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox15" value="1.3.0" tabindex="15" />
      <label for="checkbox15">Upgrade DB from 1.3.0 to 1.3.0.1</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_0_2) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox16" value="1.3.0.1" tabindex="16" />
      <label for="checkbox16">Upgrade DB from 1.3.0.1 to 1.3.0.2</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_5) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox17" value="1.3.0.2" tabindex="17" />
      <label for="checkbox17">Upgrade DB from 1.3.0.2 to 1.3.5</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_6) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox18" value="1.3.5" tabindex="18" />
      <label for="checkbox18">Upgrade DB from 1.3.5 to 1.3.6</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_7) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox19" value="1.3.6" tabindex="19" />
      <label for="checkbox19">Upgrade DB from 1.3.6 to 1.3.7</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_8) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox20" value="1.3.7" tabindex="20" />
      <label for="checkbox20">Upgrade DB from 1.3.7 to 1.3.8</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_3_9) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox21" value="1.3.8" tabindex="21" />
      <label for="checkbox21">Upgrade DB from 1.3.8 to 1.3.9</label>
    </div>
      <div class="input">
      <input <?php if ($needs_v1_5_0) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox22" value="1.3.9" tabindex="22" />
      <label for="checkbox22">Upgrade DB from 1.3.9 to 1.5.0</label>
    </div>
    <div class="input">
      <input <?php if ($needs_v1_5_1) {echo "checked";} ?> name="version[]" type="checkbox" id="checkbox23" value="1.5.0" tabindex="23" />
      <label for="checkbox22">Upgrade DB from 1.5.0 to 1.5.1</label>
    </div>
    </fieldset>
    <br />
<?php } //endif $dbinfo->zdb_configuration_table_found ?>


    <fieldset>
    <legend><strong><?php echo TITLE_DATABASE_PREFIX_CHANGE; ?></strong></legend>
<?php if (!$dbinfo->zdb_configuration_table_found) { ?>
      <?php echo ERROR_PREFIX_CHANGE_NEEDED; ?><br /><br />
      <div class="section">
        <input type="text" id="db_prefix" name="db_prefix" tabindex="40" value="<?php echo DB_PREFIX; ?>" />
        <label for="db_prefix"><?php echo DATABASE_OLD_PREFIX; ?></label>
        <p><?php echo DATABASE_OLD_PREFIX_INSTRUCTION; ?></p>
      </div>
<?php } else { // end of display field to enter "old" prefix if couldn't connect to database before ?>
      <?php echo TEXT_DATABASE_PREFIX_CHANGE; ?><br /><br />
<?php } // display normal heading ?>
      <div class="section">
      <input type="text" id="newprefix" name="newprefix" tabindex="41" value="<?php echo DB_PREFIX; ?>" />
      <label for="newprefix"><?php echo ENTRY_NEW_PREFIX; ?></label>
        <p><?php echo DATABASE_NEW_PREFIX_INSTRUCTION .'&nbsp; <a href="javascript:popupWindow(\'popup_help_screen.php?error_code=19\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
      <?php echo TEXT_DATABASE_PREFIX_CHANGE_WARNING; ?><br /><br />
    </div>
    </fieldset>
<br />

    <fieldset>
    <legend><strong><?php echo TITLE_SECURITY; ?></strong></legend>
      <?php echo ADMIN_PASSSWORD_INSTRUCTION .'&nbsp; <a href="javascript:popupWindow(\'popup_help_screen.php?error_code=78\')"> ' . TEXT_HELP_LINK . '</a>'; ?> <br /><br />
     <div class="section">
      <input type="text" id="adminid" name="adminid" tabindex="50" size="18" value="<?php echo $adminName; ?>" />
      <label for="adminid"><?php echo ENTRY_ADMIN_ID; ?></label>
     <div class="section">
    </div>
      <input type="password" id="adminpwd" name="adminpwd" tabindex="51" />
      <label for="adminpwd"><?php echo ENTRY_ADMIN_PASSWORD; ?></label>
    <br />
    </div>

    </fieldset>

    <br />&nbsp;&nbsp;<?php echo UPDATE_DATABASE_WARNING_DO_NOT_INTERRUPT; ?>&nbsp;
<?php if (isset($_GET['debug'])) echo '<input type="hidden" id="debug" name="debug" value="'.$_GET['debug'].'" />'; ?>
<?php if (isset($_GET['debug2'])) echo '<input type="hidden" id="debug2" name="debug2" value="'.$_GET['debug2'].'" />'; ?>
<?php if (isset($_GET['debug3'])) echo '<input type="hidden" id="debug3" name="debug3" value="'.$_GET['debug3'].'" />'; ?>
<?php if (isset($_GET['nogrants'])) echo '<input type="hidden" id="nogrants" name="nogrants" value="'.$_GET['nogrants'].'" />'; ?>
<?php if (isset($_POST['nogrants'])) echo '<input type="hidden" id="nogrants" name="nogrants" value="'.$_POST['nogrants'].'" />'; ?>
<br />
    <input type="submit" name="submit" class="button"  tabindex="60" value="<?php echo UPDATE_DATABASE_NOW; ?>" />
<?php if ($dbinfo->zdb_configuration_table_found) { ?>
    <input type="submit" name="skip" class="button"  tabindex="61" value="<?php echo SKIP_UPDATES; ?>" />
<?php } //endif ?>
    <input type="submit" name="refresh" class="button" tabindex="62" value="<?php echo REFRESH_BUTTON; ?>" />
<?php echo $zc_install->getConfigKeysAsPost(); ?>
    </form>