<?php

use App\Http\Controllers\ArbitrageUpLeadsController;
use App\Http\Controllers\ArkNetLeadsController;
use App\Http\Controllers\DarkLeadsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LeadvertexOrdersController;
use App\Http\Controllers\RomLeadsController;

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

Route::controller(LeadvertexOrdersController::class)->group(function () {
    Route::post('orders', 'store');
});


Route::controller(ArbitrageUpLeadsController::class)->group(function () {
    Route::post('arbitrage/leads', 'add_new_order');
});

Route::controller(ArkNetLeadsController::class)->group(function () {
    Route::post('arknet/leads', 'add_new_order'); //Get lead data from arknet and create new order on Leadvertex
});

Route::controller(DarkLeadsController::class)->group(function () {
    Route::post('darkleads', 'add_new_order');
});

Route::controller(RomLeadsController::class)->group(function () {
    Route::post('rom/leads', 'add_new_order');
});