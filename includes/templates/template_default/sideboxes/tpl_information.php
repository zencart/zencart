<?php
/**
 * Side Box Template
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Drbyte Sun Jan 7 21:28:50 2018 -0500 Modified in v1.5.6 $
 */
  $content = '';
  $content .= '<div id="' . str_replace('_', '-', $box_id . 'Content') . '" class="sideBoxContent">';
  $content .= "\n" . '<ul style="margin: 0; padding: 0; list-style-type: none;">' . "\n";
  for ($i=0, $j=sizeof($information); $i<$j; $i++) {
    $content .= '<li>' . $information[$i] . '</li>' . "\n";
  }
  $content .= '</ul>' .  "\n";
  $content .= '</div>';
