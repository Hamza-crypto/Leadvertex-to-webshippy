<?php

use App\Http\Controllers\BillingoController;
use App\Http\Controllers\BlockedUserController;
use App\Http\Controllers\LeadvertexOrdersController;
use App\Http\Controllers\NaturprimeLeadvertexController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebshippyOrdersController;
use Illuminate\Support\Facades\Route;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

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

Route::get('/test2', function () {
    // $billingo = new BillingoController();
    // $url = "/partners/1788573408";
    // $response = $billingo->createPartner();
    // echo $response;
});

Route::get('/test', function () {
    $result = "This is just test page" . time();
    echo $result;
    DiscordAlert::message($result);
});

Route::controller(WebhookController::class)->group(function () {
    Route::post('leadvertex/webhook', 'store'); // LV status: ACCEPTED
    Route::post('leadvertex-all-orders/webhook', 'createRecordOnVCC'); // LV New Order
    Route::post('webhook/arknet', 'store'); // New Order from Ark net
    // Route::get('createRecordOnComnica', 'sendData');

});

Route::controller(NaturprimeLeadvertexController::class)->group(function () {
    Route::post('naturprime_leadvertex_new_order/webhook', 'new_order_created_webhook'); // LV New Order
});

Route::controller(WebshippyOrdersController::class)->group(function () {
    Route::get('get_webshippy_orders', 'UpdateOrders');
    Route::get('/chart-data', 'chartData')->name('chart.data');
});

Route::controller(BlockedUserController::class)->group(function () {
    Route::get('blocked_users', 'index');
    Route::get('block/{id}', 'blockUser');
});

Route::controller(LeadvertexOrdersController::class)->group(function () {
    Route::get('thankyou', 'thankyou');
});

Route::get('/chart', function () {
    return view('chart');
})->name('chart');