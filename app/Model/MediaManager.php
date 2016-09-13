<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class MediaManager extends Eloquent
{
    protected $table = TABLE_MEDIA_MANAGER;
    protected $primaryKey = 'media_id';

    public function products()
    {
        return $this->belongsToMany('ZenCart\Model\Products', TABLE_MEDIA_TO_PRODUCTS, 'media_id', 'products_id');
    }
    public function clips()
    {
        return $this->hasMany('ZenCart\Model\MediaClips', 'media_id', 'media_id');
    }
}
