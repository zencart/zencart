<?php
/**
 * Page Template
 *
 * Loaded automatically by index.php?main_page=product_music_info.
 * Displays details of a music product
 *
 * @copyright Copyright 2003-2024 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: lat9 2024 Sep 17 New in v2.1.0-beta1 $
 */
?>
<!--bof Media Manager -->
<div id="mediaManager" class="productMusic">
<?php
/**
 * display the products related media clips
 */
require $template->get_template_dir('/tpl_modules_media_manager.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_media_manager.php';
?>
    <br class="clearBoth">
</div>
<br class="clearBoth">
<!--eof Media Manager -->
