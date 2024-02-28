<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddressFormat extends Model
{
    use HasFactory;

    protected $table = TABLE_ADDRESS_FORMAT;
    protected $primaryKey = 'address_format_id';
    public $timestamps = false;
}
