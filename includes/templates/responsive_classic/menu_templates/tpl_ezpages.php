<?php
/**
 * ZCAdditions.com Mega UL/LI Menu Template
 * Important Links (ez-pages) Option
 *
 * @package templateSystem
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: picaflor-azul Sun Dec 13 16:32:43 2015 -0500 New in v1.5.5 $
 * @author Altered by rbarbour (ZCAdditions.com), Mega UL/LI Menu (menus/0)
 */

  $content .= '<li><a href="javascript:void(0)">'.$title_ezpages.'</a>';
  $content .= '<ul>';

  for ($i=1, $n=sizeof($var_linksList); $i<=$n; $i++) { 
    $content .= '<li><a href="' . $var_linksList[$i]['link'] . '">' . $var_linksList[$i]['name'] . '</a></li>' . "\n" ;
  } // end FOR loop

  $content .= '</ul>';
  $content .= '</li>';
