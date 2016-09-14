<?php
/**
 * @copyright Copyright 2003-2016 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class Products
 * @package ZenCart\Model
 */
class Products extends Eloquent
{
    protected $table = TABLE_PRODUCTS;
    protected $primaryKey = 'products_id';

}
