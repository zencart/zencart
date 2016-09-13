<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class GeoZones extends Eloquent
{
    protected $table = TABLE_GEO_ZONES;
    protected $primaryKey = 'geo_zone_id';

}
