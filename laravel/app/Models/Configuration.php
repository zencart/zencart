<?php
/**
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Configuration extends Eloquent
{
    protected $table = TABLE_CONFIGURATION;
    protected $primaryKey = 'configuration_id';
    public $timestamps = false;
    protected $guarded = ['configuration_id'];
    protected $configAsIntArray = ['SECURITY_CODE_LENGTH',];
    protected $keepAsStringArray = ['PRODUCTS_MANUFACTURERS_STATUS',];

    // @todo relocate to service class
    public function loadConfigSettings()
    {
        $configs = self::all();
        foreach ($configs as $config) {
            $configValue = $config['configuration_value'];
            $convert_to_int = false; 
            if (in_array($config['configuration_key'], $this->configAsIntArray)) {
               $convert_to_int = true; 

            } else if (in_array($config['configuration_group_id'], [2,3]) && !in_array($config['configuration_key'], $this->keepAsStringArray)) {
               $convert_to_int = true; 
            }
            if ($convert_to_int) { 
                $configValue = (int)$configValue;
            }
            if (!defined(strtoupper($config['configuration_key']))) {
                define(strtoupper($config['configuration_key']), $configValue);
            }
        }
    }
}
