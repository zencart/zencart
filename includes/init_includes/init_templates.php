<?php
/**
 * initialise template system variables
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * Determines current template name for current language, from database<br />
 * Then loads template-specific locale defines, then the main language file, followed by master/default language file<br />
 * ie: includes/languages/classic/english.php followed by includes/languages/english.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: init_templates.php 3123 2006-03-06 23:36:46Z drbyte $
 */
  if (!defined('IS_ADMIN_FLAG')) {
   die('Illegal Access');
  }

/*
 * Determine the active template name, based on current language
 */
  $template_dir = '';
  $sql = "select template_dir from " . TABLE_TEMPLATE_SELECT . "
          where template_language in ('" . $_SESSION['languages_id'] . "', 0)
          UNION
          (select template_dir from " . TABLE_TEMPLATE_SELECT . "
          where template_language not in ('" . $_SESSION['languages_id'] . "', 0) order by template_language DESC, template_id)";
  $template_query = $db->Execute($sql);

  // make sure the selected template exists
  while (!$template_query->EOF) {
    $test = $template_query->fields['template_dir'];
    if (file_exists(DIR_WS_INCLUDES . 'templates/' . $test)) {
      $template_dir = $test;
      break;
    }
    $template_query->MoveNext();
  }
  if ($template_dir == '') $template_dir = 'shared';

  // notifier hook -- usually fires at breakpoint 110, so observer must be instantiated before then
  $zco_notifier->notify('NOTIFY_INIT_TEMPLATES_SELECT', NULL, $template_dir);

/**
 * The actual template directory to use
 */
  define('DIR_WS_TEMPLATE', DIR_WS_TEMPLATES . $template_dir . '/');
/**
 * The actual template images directory to use
 */
  define('DIR_WS_TEMPLATE_IMAGES', DIR_WS_TEMPLATE . 'images/');
/**
 * The actual template icons directory to use
 */
  define('DIR_WS_TEMPLATE_ICONS', DIR_WS_TEMPLATE_IMAGES . 'icons/');

/**
 *  Load locale defines specific to language+locale
 */
  if (file_exists(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $template_dir . '/locale.php')) {
    $template_dir_select = $template_dir . '/';
  } else {
    $template_dir_select = '';
  }
  require_once(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . $template_dir_select . 'locale.php');

  $zco_notifier->notify('NOTIFY_INIT_TEMPLATES_BEFORE_MAIN_LANGUAGE', NULL, $template_dir);

/**
 * Load the appropriate Language files, based on the currently-selected template
 */

  if (file_exists(DIR_WS_LANGUAGES . $template_dir . '/' . $_SESSION['language'] . '.php')) {
    $template_dir_select = $template_dir . '/';
    include_once(DIR_WS_LANGUAGES . $template_dir_select . $_SESSION['language'] . '.php');
  }
  if (file_exists(DIR_WS_LANGUAGES . 'shared' . '/' . $_SESSION['language'] . '.php')) {
    $template_dir_select = 'shared' . '/';
    include_once(DIR_WS_LANGUAGES . $template_dir_select . $_SESSION['language'] . '.php');
  }
/**
 * include the template language master (to catch all items not defined in the override file).
 * The intent here is to: load the override version to catch preferencial changes;
 * then load the original/master version to catch any defines that didn't get set into the override version during upgrades, etc.
 */
// THE FOLLOWING MIGHT NEED TO BE DISABLED DUE TO THE EXISTENCE OF function() DECLARATIONS IN MASTER ENGLISH.PHP FILE
// THE FOLLOWING MAY ALSO SEND NUMEROUS ERRORS IF YOU HAVE ERROR_REPORTING ENABLED, DUE TO REPETITION OF SEVERAL DEFINE STATEMENTS
  include_once(DIR_WS_LANGUAGES .  $_SESSION['language'] . '.php');


/**
 * send the content charset "now" so that all content is impacted by it
 */
  header("Content-Type: text/html; charset=" . CHARSET);

/**
 * include the extra language definitions
 */
  include(DIR_WS_MODULES . 'extra_definitions.php');
