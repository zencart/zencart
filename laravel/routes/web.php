<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/Xyzzy', function () {
    die('You are in a twisty maze of passageways, all alike');
});

Route::get('/frobozz', [\App\Http\Controllers\TestController::class, 'index']);
