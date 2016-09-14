<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class MediaManager
 * @package ZenCart\Model
 */
class MediaManager extends Eloquent
{
    protected $table = TABLE_MEDIA_MANAGER;
    protected $primaryKey = 'media_id';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany('ZenCart\Model\Products', TABLE_MEDIA_TO_PRODUCTS, 'media_id', 'products_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clips()
    {
        return $this->hasMany('ZenCart\Model\MediaClips', 'media_id', 'media_id');
    }
}
