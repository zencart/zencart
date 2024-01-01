<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Restive\Facades\Restive;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\AddressBookController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\CountryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'api.'], function() {
    Restive::resource('currency', CurrencyController::class);
    Restive::resource('customer', CustomerController::class);
    Restive::resource('addressbook', AddressBookController::class);
    Restive::resource('language', LanguageController::class);
    Restive::resource('country', CountryController::class);
});
