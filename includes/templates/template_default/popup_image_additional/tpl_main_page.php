<?php
/**
 * Override Template for common/tpl_main_page.php
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Jan 22 Modified in v1.5.8-alpha $
 */
?>
<body id="popupAdditionalImage" class="centeredContent" onload="resize();">
<div>
<?php
// $products_values->fields['products_image']
  if (file_exists($_GET['products_image_large_additional'])) {
    echo '<a href="javascript:window.close()">' . zen_image($_GET['products_image_large_additional'], (isset($products_values->fields['products_name']) ? $products_values->fields['products_name'] . ' ' : '') . TEXT_CLOSE_WINDOW_IMAGE) . '</a>';
  } else {
    echo '<a href="javascript:window.close()">' . zen_image(DIR_WS_IMAGES . $products_image, (isset($products_values->fields['products_name']) ? $products_values->fields['products_name'] . ' ' : '') . TEXT_CLOSE_WINDOW_IMAGE) . '</a>';
  }
?>
</div>
</body>
