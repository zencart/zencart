<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class MediaTypes extends Eloquent
{
    protected $table = TABLE_MEDIA_TYPES;
    protected $primaryKey = 'type_id';
}
