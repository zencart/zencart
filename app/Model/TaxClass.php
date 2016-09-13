<?php
/**
 * Created by PhpStorm.
 * User: wilt
 * Date: 10/09/16
 * Time: 10:22
 */

namespace ZenCart\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class TaxClass extends Eloquent
{
    protected $table = TABLE_TAX_CLASS;
    protected $primaryKey = 'tax_class_id';

}
