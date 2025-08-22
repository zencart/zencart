<?php
// -----
// Part of the "Product Options Stock" plugin by Cindy Merkin (cindy@vinosdefrutastropicales.com)
// Copyright (c) 2015-2024 Vinos de Frutas Tropicales
//
// Note: For POSM versions prior to v4.1.0, this class was embedded in the products_options_sales_report.php script.
//
// Last updated: POSM 5.0.0
//
if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

class PosmSalesReport
{
    public string $start;
    public string $end;
    public int $pID;
    public int $number_of_orders;
    public $quantity;
    public $total_price;
    public array $orders;
    public array $options;
    public $currencies;

    public function __construct($pID, $start_timestamp, $end_timestamp)
    {
        global $db;

        $this->pID = (int)$pID;
        $this->start = date('Y-m-d H:i:s', $start_timestamp);
        $this->end = date('Y-m-d H:i:s', $end_timestamp);

        $this->currencies = new currencies();

        $op_list = $db->Execute(
            "SELECT op.orders_products_id, op.final_price, op.products_quantity
               FROM " . TABLE_ORDERS_PRODUCTS . " op, " . TABLE_ORDERS . " o
              WHERE o.date_purchased >= '" . $this->start . "'
                AND o.date_purchased <= '" . $this->end . "'
                AND o.orders_id = op.orders_id
                AND op.products_id = " . $this->pID . "
                AND EXISTS (
                    SELECT opa.orders_products_attributes_id
                      FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
                     WHERE opa.orders_products_id = op.orders_products_id
                     LIMIT 1
                )"
        );
        $this->number_of_orders = (int)$op_list->RecordCount();
        $this->total_price = 0;
        $this->quantity = 0;
        $this->orders = [];
        $this->options = [];
        foreach ($op_list as $op) {
            $opID = $op['orders_products_id'];
            $quantity = $op['products_quantity'];
            $product_price = $op['final_price'] * $quantity;
            $this->quantity += $quantity;
            $this->total_price += $product_price;

            $opa_list = $db->Execute(
                "SELECT products_options as options_name, products_options_values as options_values_name, products_options_id as options_id, products_options_values_id as options_values_id
                   FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . "
                  WHERE orders_products_id = $opID 
               ORDER BY orders_products_attributes_id ASC"
            );
            $options = '';
            $options_array = [];
            foreach ($opa_list as $opa) {
                $options_id = $opa['options_id'];
                $options_name = $opa['options_name'];
                $options_values_id = $opa['options_values_id'];
                $options_values_name = ($options_values_id === '0') ? TEXT_PRODUCT_DISPLAY : $opa['options_values_name'];

                $options .= '{' . $options_id . ':' . $options_values_id . '}';
                $options_array[$options_id] = $options_values_id;

                if (!isset($this->options[$options_id])) {
                    $this->options[$options_id] = [
                        'names' => [],
                        'values' => [],
                    ];
                }
                if (!in_array($options_name, $this->options[$options_id]['names'])) {
                    $this->options[$options_id]['names'][] = $options_name;
                }

                if (!isset($this->options[$options_id]['values'][$options_values_id])) {
                    $this->options[$options_id]['values'][$options_values_id] = [
                        'names' => [],
                        'number_of_orders' => 0,
                        'quantity' => 0,
                        'total_price' => 0
                    ];
                }
                if (!in_array($options_values_name, $this->options[$options_id]['values'][$options_values_id]['names'])) {
                    $this->options[$options_id]['values'][$options_values_id]['names'][] = $options_values_name;
                }
                $this->options[$options_id]['values'][$options_values_id]['number_of_orders']++;
                $this->options[$options_id]['values'][$options_values_id]['quantity'] += $quantity;
                $this->options[$options_id]['values'][$options_values_id]['total_price'] += $product_price;
            }

            $options_hash = hash('md5', $options);
            if (!isset($this->orders[$options_hash])) {
                $this->orders[$options_hash] = [
                    'options' => $options_array,
                    'number_of_orders' => 0,
                    'quantity' => 0,
                    'total_price' => 0,
                ];
            }
            $this->orders[$options_hash]['number_of_orders']++;
            $this->orders[$options_hash]['quantity'] += $quantity;
            $this->orders[$options_hash]['total_price'] += $product_price;
        }
    }

    public function get_order_count(): int
    {
        return $this->number_of_orders;
    }

    public function get_product_total_quantity()
    {
        return $this->quantity;
    }

    public function get_product_total_price(bool $format = true)
    {
        return ($format === true) ? $this->currencies->format($this->total_price) : $this->total_price;
    }

    public function get_option_count(): int
    {
        return count($this->options);
    }

    public function getOptionNames(): array
    {
        $option_names = [];
        foreach ($this->options as $options_id => $info) {
            $option_names[$options_id] = $info['names'];
        }
        return $option_names;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }
}
