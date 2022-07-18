<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class PluginControl extends Eloquent
{
    protected $table = TABLE_PLUGIN_CONTROL;
    protected $primaryKey = 'unique_key';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
      
    public function getRelativePath()
    {
        $relativePath =  ($GLOBALS['request_type'] == 'SSL' ? DIR_WS_HTTPS_CATALOG : DIR_WS_CATALOG). 'zc_plugins/' . $this->unique_key . '/' . $this->version . '/';
        return $relativePath;
    }
    
    public function getAbsolutePath()
    {
        $absolutePath = DIR_FS_CATALOG . 'zc_plugins/' . $this->unique_key . '/' . $this->version . '/';
        return $absolutePath;
    }
    
}