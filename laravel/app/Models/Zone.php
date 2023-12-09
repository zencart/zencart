<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Zone extends Eloquent
{
    protected $table = TABLE_ZONES;
    protected $primaryKey = 'zone_id';
    public $timestamps = false;
    protected $guarded = [];

    public function country()
    {
        return $this->hasOne(Country::class, 'countries_id', 'zone_country_id');
    }
}
