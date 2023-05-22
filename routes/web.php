<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Http;
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

Route::get('/test', function () {
    echo "This is just test page " . time();
});

Route::controller(WebhookController::class)->group(function () {
    Route::post('leadvertex/webhook', 'store');
    Route::post('leadvertex-all-orders/webhook', 'createRecordOnComnica');
    // Route::get('createRecordOnComnica', 'sendData');

});
