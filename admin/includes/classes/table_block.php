<?php

/**
 * @package admin
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Zen4All Thu Nov 9 12:37:01 2017 +0100 Modified in v1.5.6 $
 */
class boxTableBlock {

  function tableBlock($contents) {
    $tableBox_string = '';

    $form_set = false;
    if (isset($contents['form'])) {
      $tableBox_string .= $contents['form'] . "\n";
      $form_set = true;
      array_shift($contents);
    }

    for ($i = 0, $n = sizeof($contents); $i < $n; $i++) {
      if (isset($contents[$i][0]) && is_array($contents[$i][0])) {
        for ($x = 0, $y = sizeof($contents[$i]); $x < $y; $x++) {
          if (isset($contents[$i][$x]['text']) && zen_not_null($contents[$i][$x]['text'])) {
            $tableBox_string .= '<div class="row';
            if (zen_not_null($this->table_row_parameters)) {
              $tableBox_string .= ' ' . $this->table_row_parameters;
            }
            if (isset($contents[$i][$x]['align']) && zen_not_null($contents[$i][$x]['align'])) {
              $tableBox_string .= ' ' . $contents[$i][$x]['align'];
            }
            if (isset($contents[$i][$x]['params']) && zen_not_null($contents[$i][$x]['params'])) {
              $tableBox_string .= ' ' . $contents[$i][$x]['params'];
            } elseif (zen_not_null($this->table_data_parameters)) {
              $tableBox_string .= ' ' . $this->table_data_parameters;
            }
            $tableBox_string .= '"';
            $tableBox_string .= '>';
            if (isset($contents[$i][$x]['form']) && zen_not_null($contents[$i][$x]['form'])){
              $tableBox_string .= $contents[$i][$x]['form'];
            }
            $tableBox_string .= $contents[$i][$x]['text'];
            if (isset($contents[$i][$x]['form']) && zen_not_null($contents[$i][$x]['form'])){
              $tableBox_string .= '</form>';
            }
            $tableBox_string .= '</div>' . "\n";
          }
        }
      } else {
        $tableBox_string .= '<div class="row';
        if (isset($contents[$i]['align']) && zen_not_null($contents[$i]['align'])) {
          $tableBox_string .= ' ' . $contents[$i]['align'];
        }
        if (isset($contents[$i]['params']) && zen_not_null($contents[$i]['params'])) {
          $tableBox_string .= ' ' . $contents[$i]['params'];
        } elseif (zen_not_null($this->table_data_parameters)) {
          $tableBox_string .= ' ' . $this->table_data_parameters;
        }
        $tableBox_string .= '"';
        $tableBox_string .= '>' . $contents[$i]['text'] . '</div>' . "\n";
      }
    }

    if ($form_set == true) {
      $tableBox_string .= '</form>' . "\n";
    }

    return $tableBox_string;
  }

}
