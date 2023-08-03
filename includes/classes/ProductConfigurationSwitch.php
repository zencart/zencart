<?php 
/**
 * Class ProductConfigurationSwitch 
 *
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson 2022 Aug 15 Modified in v1.5.8-alpha2 $
 */

class ProductConfigurationSwitch extends base
{
    protected $layout_data = [];
    protected $configuration_data = [];
    protected $prefix, $suffix,  $field_prefix, $field_suffix; 
    protected $type_handler;
    protected $products_type;

    public function __construct($lookup, $prefix = 'SHOW_', $suffix = '_INFO', $field_prefix = '_', $field_suffix = '') 
    {
        global $db; 
  
        $this->prefix = $prefix; 
        $this->suffix = $suffix; 
        $this->field_prefix = $field_prefix; 
        $this->field_suffix = $field_suffix; 
  
        $sql = "SELECT products_type FROM " . TABLE_PRODUCTS . " WHERE products_id=" . (int)$lookup;
        $type_lookup = $db->Execute($sql, 1);
  
        if ($type_lookup->RecordCount() == 0) {
          return false;
        }

        $this->products_type = $type_lookup->fields['products_type']; 

        $sql = "SELECT type_handler FROM " . TABLE_PRODUCT_TYPES . " WHERE type_id = " . (int)$type_lookup->fields['products_type']; 
        $show_key = $db->Execute($sql, 1);
  
        $this->type_handler = $show_key->fields['type_handler'];
  
        $zv_key = strtoupper($prefix . $this->type_handler . $suffix . $field_prefix . "%" . $field_suffix);
  
        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_PRODUCT_TYPE_LAYOUT . " WHERE configuration_key LIKE '" . zen_db_input($zv_key) . "'";
        $zv_key_values = $db->Execute($sql);
  
        foreach ($zv_key_values as $entry) {
          $this->layout_data[$entry['configuration_key']] = $entry['configuration_value']; 
        }
  
        $sql = "SELECT configuration_key, configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE '" . zen_db_input($zv_key) . "'";
        $zv_key_values = $db->Execute($sql);
  
        foreach ($zv_key_values as $entry) {
          $this->configuration_data[$entry['configuration_key']] = $entry['configuration_value']; 
        }
    }

    public function getSwitch($field) 
    {
        $switch = strtoupper($this->prefix . $this->type_handler . $this->suffix . $this->field_prefix . $field . $this->field_suffix);
        if (isset($this->layout_data[$switch])) { 
           return $this->layout_data[$switch]; 
        } else if (isset($this->configuration_data[$switch])) { 
           return $this->configuration_data[$switch]; 
        } else { 
           return false;
        }
    }

    public function getProductsType()
    {
       return $this->products_type;
    }
}
