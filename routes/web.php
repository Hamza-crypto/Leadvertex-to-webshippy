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
Route::view('/', 'welcome');

Route::get('/test', function () {
    $result = "This is just test page for LV2 " . time();
    echo  $result;
    DiscordAlert::message($result);
});

Route::controller(WebhookController::class)->group(function () {
    Route::post('leadvertex/status-change', 'store'); // LV status: ACCEPTED
//    Route::post('leadvertex-all-orders/webhook', 'createRecordOnComnica'); // LV New Order
    // Route::get('createRecordOnComnica', 'sendData');

});

Route::controller(WebshippyOrdersController::class)->group(function () {
    Route::get('get_webshippy_orders', 'UpdateOrders');
    Route::get('/chart-data', 'chartData')->name('chart.data');
});

Route::get('/chart', function () {
    return view('chart');
})->name('chart');
