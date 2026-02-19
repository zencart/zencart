<?php
/**
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * @since ZC v1.5.8
 */
class AddressFormat extends Eloquent
{
    protected $table = TABLE_ADDRESS_FORMAT;
    protected $primaryKey = 'address_format_id';
    public $timestamps = false;
}
