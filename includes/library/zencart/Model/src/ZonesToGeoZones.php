<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ZonesToGeoZones extends Eloquent
{
    protected $table = TABLE_ZONES_TO_GEO_ZONES;
    protected $primaryKey = 'association_id';
}
