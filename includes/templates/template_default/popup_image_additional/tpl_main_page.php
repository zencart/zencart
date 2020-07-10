<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: mc12345678 2020 Jan 21 Modified in v1.5.7 $
 */
?>
<body id="popupAdditionalImage" class="centeredContent" onload="resize();">
<div>
<?php
// $products_values->fields['products_image']
  if (file_exists($_GET['products_image_large_additional'])) {
    echo '<a href="javascript:window.close()">' . zen_image($_GET['products_image_large_additional'], (isset($products_values->fields['products_name']) ? $products_values->fields['products_name'] . ' ' : '') . TEXT_CLOSE_WINDOW) . '</a>';
  } else {
    echo '<a href="javascript:window.close()">' . zen_image(DIR_WS_IMAGES . $products_image, (isset($products_values->fields['products_name']) ? $products_values->fields['products_name'] . ' ' : '') . TEXT_CLOSE_WINDOW) . '</a>';
  }
?>
</div>
</body>