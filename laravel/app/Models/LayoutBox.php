<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayoutBox extends Model
{
    use HasFactory;

    protected $table = TABLE_LAYOUT_BOXES;
    protected $primaryKey = 'layout_id';
    public $timestamps = false;
    protected $guarded = [];
}
