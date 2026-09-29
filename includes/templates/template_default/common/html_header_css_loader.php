<?php
/**
 * Common Template
 *
 * Outputs the html header's CSS files. CSS files are loaded parent-first, with the active template's
 * version of the file being loaded last (and, thus, taking precedence in a page's styling).
 *
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2025 May 02 Modified in v2.2.0 $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * load all template-specific stylesheets, named like "style*.css", alphabetically.
 */
foreach ($template->getTemplateFilesWithDir('^style.*\.css', $current_page_base, 'css') as $next_css_file) {
    echo '<link rel="stylesheet" href="' . zen_add_filemtime($next_css_file) . '">' . "\n";
}

/**
 * load stylesheets on a per-page/per-language/per-product/per-manufacturer/per-category basis. Concept by Juxi Zoza.
 */
$manufacturers_id = $_GET['manufacturers_id'] ?? '';
$tmp_products_id = (int)($_GET['products_id'] ?? 0);
$tmp_pagename = ($this_is_home_page) ? 'index_home' : $current_page_base;
if ($current_page_base === 'page' && isset($ezpage_id)) {
    $tmp_pagename = $current_page_base . (int)$ezpage_id;
}
$sheets_array = [
    $_SESSION['language'] . '_stylesheet',
    $tmp_pagename,
    $_SESSION['language'] . '_' . $tmp_pagename,
    'c_' . $cPath,
    $_SESSION['language'] . '_c_' . $cPath,
    'm_' . $manufacturers_id,
    $_SESSION['language'] . '_m_' . (int)$manufacturers_id,
    'p_' . $tmp_products_id,
    $_SESSION['language'] . '_p_' . $tmp_products_id,
];
foreach ($sheets_array as $value) {
    foreach ($template->getTemplateFilesWithDir('^' . $value . '\.css', $current_page_base, 'css') as $next_css_file) {
        echo '<link rel="stylesheet" href="' . zen_add_filemtime($next_css_file) . '">' . "\n";
    }
}

/**
 *  custom category handling for a parent and all its children ... works for any c_XX_XX_children.css  where XX_XX is any parent category
 */
$tmp_cats = explode('_', $cPath);
$value = '';
foreach ($tmp_cats as $val) {
    $value .= $val;

    $ppfile = 'c_' . $value . '_children';
    foreach ($template->getTemplateFilesWithDir('^' . $ppfile . '\.css', $current_page_base, 'css') as $next_css_file) {
        echo '<link rel="stylesheet" href="' . zen_add_filemtime($next_css_file) . '">' . "\n";
    }

    $ppfile = $_SESSION['language'] . '_' . $ppfile;
    foreach ($template->getTemplateFilesWithDir('^' . $ppfile . '\.css', $current_page_base, 'css') as $next_css_file) {
        echo '<link rel="stylesheet" href="' . zen_add_filemtime($next_css_file) . '">' . "\n";
    }

    $value .= '_';
}

/**
 * load printer-friendly stylesheets -- named like "print*.css", alphabetically
 */
foreach ($template->getTemplateFilesWithDir('^print.*\.css', $current_page_base, 'css') as $next_css_file) {
    echo '<link rel="stylesheet" media="print" href="' . zen_add_filemtime($next_css_file) . '">' . "\n";
}

/**
 * load all DYNAMIC template-specific stylesheets, named like "style*.php", alphabetically
 */
foreach ($template->getTemplateFilesWithDir('^style.*\.php', $current_page_base, 'css') as $next_css_file) {
    require $next_css_file;
}

// User defined styles come last
foreach ($template->getTemplateFilesWithDir('^site_specific_styles\.php', $current_page_base, 'css') as $next_user_style) {
    require $next_user_style;
}
