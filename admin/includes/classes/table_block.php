<?php

/**
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: brittainmark 2022 Aug 22 Modified in v1.5.8-alpha2 $
 * @since ZC v1.5.5
 */
class boxTableBlock
{
    protected string $table_row_parameters;
    protected string $table_data_parameters;

    /**
     * @since ZC v1.5.5
     */
    public function tableBlock($contents): string
    {
        $tableBox_string = '';

        $form_set = false;
        if (isset($contents['form'])) {
            $tableBox_string .= $contents['form'] . "\n";
            $form_set = true;
            array_shift($contents);
        }

        foreach ($contents as $rowKey => $row) {
            if (isset($row[0]) && is_array($row[0])) {
                foreach ($row as $cell => $content) {
                    if (isset($content['text']) && zen_not_null($content['text'])) {
                        $tableBox_string .= '<div class="row';
                        if (zen_not_null($this->table_row_parameters)) {
                            $tableBox_string .= ' ' . $this->table_row_parameters;
                        }
                        if (isset($content['align']) && zen_not_null($content['align'])) {
                            $tableBox_string .= ' ' . $content['align'];
                        }
                        if (isset($content['params']) && zen_not_null($content['params'])) {
                            $tableBox_string .= ' ' . $content['params'];
                        } elseif (zen_not_null($this->table_data_parameters)) {
                            $tableBox_string .= ' ' . $this->table_data_parameters;
                        }
                        $tableBox_string .= '"';
                        $tableBox_string .= '>';
                        if (isset($content['form']) && zen_not_null($content['form'])) {
                            $tableBox_string .= $contents[$rowKey][$cell]['form'];
                        }
                        $tableBox_string .= $contents[$rowKey][$cell]['text'];
                        if (isset($content['form']) && zen_not_null($content['form'])) {
                            $tableBox_string .= '</form>';
                        }
                        $tableBox_string .= '</div>' . "\n";
                    }
                }
            } else {
                $tableBox_string .= '<div class="row';
                if (isset($row['align']) && zen_not_null($row['align'])) {
                    $tableBox_string .= ' ' . $row['align'];
                }
                if (isset($row['params']) && zen_not_null($row['params'])) {
                    $tableBox_string .= ' ' . $row['params'];
                } elseif (zen_not_null($this->table_data_parameters)) {
                    $tableBox_string .= ' ' . $this->table_data_parameters;
                }
                $tableBox_string .= '"';
                $tableBox_string .= '>' . $row['text'] . '</div>' . "\n";
            }
        }

        if ($form_set === true) {
            $tableBox_string .= '</form>' . "\n";
        }

        return $tableBox_string;
    }

}
