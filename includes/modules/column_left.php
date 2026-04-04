<?php
/**
 * column_left module
 *
 * @copyright Copyright 2003-2026 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2026 Feb 26 Modified in v2.2.1 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
use Zencart\DbRepositories\LayoutBoxRepository;
use Zencart\ResourceLoaders\SideboxFinder;
use Zencart\FileSystem\FileSystem;

$column_box_default='tpl_box_default_left.php';
// Check if there are boxes for the column
global $db;
$layoutBoxRepository = new LayoutBoxRepository($db);
$sideboxes = $layoutBoxRepository->getActiveForLocation(0, $template_dir, 100);

$column_width = (int)BOX_WIDTH_LEFT;
foreach ($sideboxes as $sidebox) {
    $boxFile = (new SideboxFinder(new FileSystem))->sideboxPath($sidebox, $template_dir, true);
    if ($boxFile !== false) {
        $box_id = zen_get_box_id($sidebox['layout_box_name']);
        include($boxFile . $sidebox['layout_box_name']);
    }
}
$box_id = '';
