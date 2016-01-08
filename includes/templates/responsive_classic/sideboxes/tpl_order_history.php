<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2015 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: picaflor-azul Modified in v1.5.5 $
 */
  $content = "";
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">' . "\n";
  $content .= '<ul class="orderHistList">' . "\n" ;

  for ($i=1; $i<=sizeof($customer_orders); $i++) {

        $content .= '<li><a href="' . zen_href_link(zen_get_info_page($customer_orders[$i]['id']), 'products_id=' . $customer_orders[$i]['id']) . '">' . $customer_orders[$i]['name'] . '</a><a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=cust_order&pid=' . $customer_orders[$i]['id']) . '"><i class="fa fa-cart-arrow-down"></i></a></li>' . "\n" ;
  }
  $content .= '</ul>' . "\n" ;
  $content .= '</div>';
