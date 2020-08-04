<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ProductTypeLayout extends Eloquent
{
    protected $table = TABLE_PRODUCT_TYPE_LAYOUT;
    protected $primaryKey = 'configuration_id';
    public $timestamps = false;


    public function loadConfigSettings()
    {
        $configs = $this->all();
        foreach ($configs as $config) {
            define(strtoupper($config['configuration_key']), $config['configuration_value']);
        }
    }
}
