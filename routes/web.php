<?php

use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebshippyOrdersController;
use App\Notifications\LeadVertexNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Spatie\DiscordAlerts\Facades\DiscordAlert;
use Illuminate\Support\Facades\Notification;

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
    $result = "This is just test page " . time();
    echo  $result;
    DiscordAlert::message($result);
});

Route::controller(WebhookController::class)->group(function () {
    Route::post('leadvertex/webhook', 'store');
    Route::post('leadvertex-all-orders/webhook', 'createRecordOnComnica');
    // Route::get('createRecordOnComnica', 'sendData');

});

Route::get('get_webshippy_orders', [WebshippyOrdersController::class, 'UpdateOrders']);
