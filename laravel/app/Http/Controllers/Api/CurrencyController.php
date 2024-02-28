<?php
namespace App\Http\Controllers\Api;

use App\Models\Currency;
use Restive\Http\Controllers\ApiController;

class CurrencyController extends ApiController
{
    protected string $modelName = Currency::class;
}
