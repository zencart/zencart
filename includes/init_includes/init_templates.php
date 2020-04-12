<?php
/**
 * initialise template system variables
 * see {@link  http://www.zen-cart.com/wiki/index.php/Developers_API_Tutorials#InitSystem wikitutorials} for more details.
 *
 * Determines current template name for current language, from database<br />
 * Then loads template-specific language file, followed by master/default language file<br />
 * ie: includes/languages/classic/english.php followed by includes/languages/english.php
 *
 * @package initSystem
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id:  Modified in v1.5.7 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/*
 * Lookup the template for the current language
 * The 'choice' aliases help with weighting for fallback to default selection
 */
$template_dir = 'template_default';
$sql = "SELECT template_dir, template_language, template_language=" . (int)$_SESSION['languages_id'] . " AS choice1, template_language=0 AS choice2
        FROM " . TABLE_TEMPLATE_SELECT . "
        ORDER BY choice1 DESC, choice2 DESC, template_language";
$result = $db->Execute($sql);
$template_dir = $result->fields['template_dir'];

/**
 * Allow admins to switch templates using &t= URL parameter
 */
if (false !== strpos(EXCLUDE_ADMIN_IP_FOR_MAINTENANCE, $_SERVER['REMOTE_ADDR'])) {
    $templates = array();
    foreach($result as $row) {
        $templates[] = $row['template_dir'];
    }
    // check if a template override was requested and that it matches an available choice and it exists on the filesystem
    if (isset($_GET['t']) && in_array($_GET['t'], $templates, true) && file_exists(DIR_WS_TEMPLATES . $_GET['t'])) {
        $_SESSION['tpl_override'] = $_GET['t'];
    }
    if (isset($_SESSION['tpl_override'])) $template_dir = $_SESSION['tpl_override'];
    unset($templates, $row);
}


/**
 * Now that we've established which template to use, initialize all its components
 */

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
 * Load the appropriate Language files, based on the currently-selected template
 */

  include_once(zen_get_file_directory(DIR_FS_CATALOG . DIR_WS_LANGUAGES, $_SESSION['language'].'.php', 'false'));

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
  include(DIR_WS_MODULES . zen_get_module_directory('extra_definitions.php'));
?>
