<?php
/**
 * Template Information File
 *
 * @copyright Copyright 2003-2023 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2022 Nov 29 Modified in v1.5.8a $
 */
$template_name = 'Default Template';
$template_version = 'Version 1.0';
$template_author = 'Zen Cart Team (c) 2003';
$template_description = 'This template set is designed to be easily modified using only the style sheet to change colors, fonts, and the store logo. Three images are required; logo.jpg, header_bg.jpg, and tile_back.jpg.';
$template_screenshot = 'scr_template_default.jpg';

// -----
// Normally, this template does not use "Single Column Settings" in the admin's "Layout Controller",
// but if a site has set the value, the template honors that setting.
//
$uses_single_column_layout_settings = (isset($uses_single_column_layout_settings)) ? $uses_single_column_layout_settings : false;
