<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;

    protected $table = TABLE_PRODUCT_TYPES;
    protected $primaryKey = 'type_id';
    public $timestamps = false;

}
