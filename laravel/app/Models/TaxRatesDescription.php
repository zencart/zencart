<?php
/**
 * @copyright Copyright 2003-2025 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version GIT: $Id: $
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class TaxRatesDescription extends Eloquent
{
    protected $table = TABLE_TAX_RATES_DESCRIPTION;
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];

    public function rate()
    {
        return $this->hasOne(TaxRate::class, 'tax_rates_id', 'tax_rates_id');
    }
}
