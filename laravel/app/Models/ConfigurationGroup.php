<?php
/**
 * @copyright Copyright 2003-2021 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigurationGroup extends Model
{
    use HasFactory;

    protected $table = TABLE_CONFIGURATION_GROUP;
    protected $primaryKey = 'configuration_group_id';
    public $timestamps = false;

}
