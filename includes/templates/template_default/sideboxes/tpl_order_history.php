<?php
/**
 * Side Box Template
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2020 Jun 19 Modified in v1.5.7 $
 */
$content = "";
$content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">' . "\n";
$content .= '<ul class="list-links orderHistList">' . "\n" ;

foreach ($customer_orders as $row) {
  $content .= '
<li>
<a href="' . zen_href_link(zen_get_info_page($row['id']), 'products_id=' . $row['id']) . '">' . $row['name'] . '</a>&nbsp;&nbsp;
<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $row['id']) . '">' . zen_image($template->get_template_dir(ICON_IMAGE_TINYCART, DIR_WS_TEMPLATE, $current_page_base,'images/icons'). '/' . ICON_IMAGE_TINYCART, ICON_TINYCART_ALT) . '</a>
</li>
';
  }
$content .= '</ul>' . "\n" ;
$content .= '</div>';
