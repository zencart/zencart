<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class LayoutBox extends Eloquent
{
    protected $table = TABLE_LAYOUT_BOXES;
    protected $primaryKey = 'layout_id';
    public $timestamps = false;
    protected $guarded = [];
}
