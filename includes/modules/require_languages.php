<?php
/**
 * loads template- and page-specific language override files
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Jul 09 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$languageLoader->setCurrentPage($current_page);
$languageLoader->loadLanguageForView();
