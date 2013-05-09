<?php
/**
 * @package Installer
 * @access private
 * @copyright Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: database_setup_default.php 18830 2011-05-31 23:11:45Z drbyte $
 */

  if ($zc_install->error) include(DIR_WS_INSTALL_TEMPLATE . 'templates/display_errors.php');
?>
    <form method="post" action="index.php?main_page=database_setup<?php echo zcInstallAddSID(); ?>">
    <fieldset>
    <legend><?php echo DATABASE_INFORMATION; ?></legend>
      <div class="section">
      <input type="hidden" id="db_type" name="db_type" value="mysql" />
      <select id="db_coll" name="db_coll" tabindex="1">
        <option value="latin1"<?php echo setSelected('latin1', $_POST['db_coll']); ?>>Latin1</option>
        <option value="utf8"<?php echo setSelected('utf8', $_POST['db_coll']); ?>>UTF-8 (default)</option>
      </select>
        <label for="db_type"><?php echo DATABASE_CHARSET; ?></label>
      <p><?php echo DATABASE_CHARSET_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=14\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="text" id="db_host" name="db_host" tabindex="2" value="<?php echo DATABASE_HOST_VALUE; ?>" size="18" />
      <label for="db_host"><?php echo DATABASE_HOST; ?></label>
      <p><?php echo DATABASE_HOST_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=15\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="text" id="db_username" name="db_username" tabindex="3" value="<?php echo DATABASE_USERNAME_VALUE; ?>" size="18" />
      <label for="db_username"><?php echo DATABASE_USERNAME; ?></label>
      <p><?php echo DATABASE_USERNAME_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=16\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="password" id="db_pass" name="db_pass" tabindex="4" />
      <label for="db_pass"><?php echo DATABASE_PASSWORD; ?></label>
      <p><?php echo DATABASE_PASSWORD_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=17\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    <div class="section">
      <input type="text" id="db_name" name="db_name" tabindex="5" value="<?php echo DATABASE_NAME_VALUE; ?>" size="18" />
      <label for="db_name"><?php echo DATABASE_NAME; ?></label>
      <p><?php echo DATABASE_NAME_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=18\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
    </fieldset>
    <fieldset>
    <legend><?php echo DATABASE_OPTIONAL_INFORMATION; ?></legend>
    <p class="attention"><?php echo DATABASE_OPTIONAL_INSTRUCTION; ?></p>
<?php if (!$is_upgrade || $zc_install->fatal_error) { //do not display prefix field if upgrading ... prefix can be edited on the database-upgrade page, next. ?>
    <div class="section">
      <input type="text" id="db_prefix" name="db_prefix" tabindex="11" value="<?php echo DATABASE_NAME_PREFIX; ?>" size="18" />
      <label for="db_prefix"><?php echo DATABASE_PREFIX; ?></label>
      <p><?php echo DATABASE_PREFIX_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=19\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
<?php } else { ?>
      <input type="hidden" id="db_prefix" name="db_prefix" value="<?php echo DATABASE_NAME_PREFIX; ?>" />
<?php } ?>

      <div class="section">
      <select id="cache_type" name="cache_type" tabindex="13">
        <option value="none"<?php echo setSelected('none', $_POST['cache_type']); ?>>None</option>
        <option value="file"<?php echo setSelected('file', $_POST['cache_type']); ?>>File</option>
        <option value="database"<?php echo setSelected('database', $_POST['cache_type']); ?>>Database</option>
      </select>
        <label for="cache_type"><?php echo CACHE_TYPE; ?></label>
      <p><?php echo CACHE_TYPE_INSTRUCTION . '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=60\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
                <div class="section">
      <input type="text" id="sql_cache_dir" name="sql_cache_dir" tabindex="14" size="55" value="<?php echo SQL_CACHE_VALUE; ?>" />
      <label for="sql_cache_dir"><?php echo SQL_CACHE; ?></label>
      <p><?php echo SQL_CACHE_INSTRUCTION. '<a href="javascript:popupWindow(\'popup_help_screen.php?error_code=59\')"> ' . TEXT_HELP_LINK . '</a>'; ?></p>
    </div>
          </fieldset>
<?php if (isset($_GET['debug'])) echo '<input type="hidden" id="debug" name="debug" value="'.$_GET['debug'].'" />'; ?>
<?php if (isset($_GET['debug2'])) echo '<input type="hidden" id="debug2" name="debug2" value="'.$_GET['debug2'].'" />'; ?>
<?php if (isset($_GET['debug3'])) echo '<input type="hidden" id="debug3" name="debug3" value="'.$_GET['debug3'].'" />'; ?>
<?php if (isset($_GET['configfile'])) echo '<input type="hidden" id="configfile" name="configfile" value="'.$_GET['configfile'].'" />'; ?>
<?php if (isset($_GET['nogrants'])) echo '<input type="hidden" id="nogrants" name="nogrants" value="'.$_GET['nogrants'].'" />'; ?>
    <input type="submit" name="submit" class="button" tabindex="20" value="<?php echo SAVE_DATABASE_SETTINGS; ?>" />
<?php if (FALSE && $write_config_files_only) { ?>
    <input type="submit" name="submit" class="button" tabindex="21" value="<?php echo ONLY_UPDATE_CONFIG_FILES; ?>" />
<?php } ?>

<?php echo $zc_install->getConfigKeysAsPost(); ?>
    </form>
