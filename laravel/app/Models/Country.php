<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = TABLE_COUNTRIES;
    protected $primaryKey = 'countries_id';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'status' => 'boolean',
    ];

    public function addressFormat()
    {
        return $this->belongsTo(AddressFormat::class, 'address_format_id', 'address_format_id');
    }
}
