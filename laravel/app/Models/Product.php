<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = TABLE_PRODUCTS;
    protected $primaryKey = 'products_id';
    public $timestamps = false;

    public function productType()
    {
        return $this->hasOne(ProductType::class, 'type_id', 'product_type');
    }
}
