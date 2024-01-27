<?php
/**
 * Header code file for the Search Input page
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: neekfenwick 2023 Dec 09 Modified in v2.0.0-alpha1 $
 */
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
$breadcrumb->add(NAVBAR_TITLE_1);

//test:
//&keyword=die+hard&categories_id=10&inc_subcat=1&manufacturers_id=4&pfrom=1&pto=50&dfrom=01%2F01%2F2003&dto=12%2F20%2F2005

$sData = [];
$sData['keyword'] = stripslashes(isset($_GET['keyword']) ? zen_output_string_protected($_GET['keyword']) : '');
$sData['search_in_description'] = (isset($_GET['search_in_description']) ? zen_output_string((int)$_GET['search_in_description']) : 1);
$sData['categories_id'] = (isset($_GET['categories_id']) ? zen_output_string((int)$_GET['categories_id']) : 0);
$sData['inc_subcat'] = (isset($_GET['inc_subcat']) ? zen_output_string((int)$_GET['inc_subcat']) : 1);
$sData['manufacturers_id'] = (isset($_GET['manufacturers_id']) ? zen_output_string((int)$_GET['manufacturers_id']) : 0);
$sData['dfrom'] = (isset($_GET['dfrom']) ? zen_output_string($_GET['dfrom']) : '');
$sData['dto'] = (isset($_GET['dto']) ? zen_output_string($_GET['dto']) : '');
$sData['pfrom'] = (isset($_GET['pfrom']) ? zen_output_string($_GET['pfrom']) : '');
$sData['pto'] = (isset($_GET['pto']) ? zen_output_string($_GET['pto']) : '');

// check manufacturers
$result = $db->Execute("SELECT manufacturers_id FROM " . TABLE_MANUFACTURERS . " LIMIT 1");
$skip_manufacturers = ($result->EOF);
