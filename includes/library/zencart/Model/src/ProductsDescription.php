<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ProductsDescription extends Eloquent
{
    protected $table = TABLE_PRODUCTS_DESCRIPTION;
    protected $primaryKey = 'products_id';
    public $incrementing = false;

}
