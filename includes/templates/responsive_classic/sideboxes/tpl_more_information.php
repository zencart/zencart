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
  $content = '';
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">' . "\n" ;
  $content .=  "\n" . '<ul class="list-links">' . "\n" ;
  for ($i=0; $i<sizeof($more_information); $i++) {
    $content .= '<li>' . $more_information[$i] . '</li>' . "\n" ;
  }

  $content .= '</ul>' . "\n" ;
  $content .= '</div>';
