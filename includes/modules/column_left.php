<?php
/**
 * column_left module
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jul 09 Modified in v1.5.8-alpha $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
use App\Models\LayoutBox;
use Zencart\ResourceLoaders\SideboxFinder;
use Zencart\FileSystem\FileSystem;

$column_box_default='tpl_box_default_left.php';
// Check if there are boxes for the column
$sideboxes = LayoutBox::where('layout_box_location', 0)
    ->where('layout_box_status', 1)
    ->where('layout_template', $template_dir)
    ->orderBy('layout_box_sort_order')
    ->limit(100)->get();

$column_width = (int)BOX_WIDTH_LEFT;
foreach ($sideboxes as $sidebox) {
    $boxFile = (new SideboxFinder(new FileSystem))->sideboxPath($sidebox, $template_dir, true);
    if ($boxFile !== false) {
        $box_id = zen_get_box_id($sidebox['layout_box_name']);
        include($boxFile . $sidebox['layout_box_name']);
    }
}
$box_id = '';
