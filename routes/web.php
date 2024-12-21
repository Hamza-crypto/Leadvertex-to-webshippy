<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BlockedUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveoController;
use App\Http\Controllers\DoctorVisitController;
use App\Http\Controllers\FacebookWebhookController;
use App\Http\Controllers\LeadvertexOrdersController;
use App\Http\Controllers\NaturprimeLeadvertexController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SzamlaController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WebshippyOrdersController;
use App\Http\Controllers\WebshopPriceController;
use App\Http\Controllers\ZappierController;
use App\Notifications\LeadVertexNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
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
// Route::view('/', 'welcome');
Route::redirect('/', 'dashboard');
Route::view('privacy-policy', 'privacy-policy');

Route::get('/optimize', function () {
    Artisan::call('optimize:clear');
    dump('Cache cleared successfully');
});

Route::get('/migrate/fresh', function () {
    Artisan::call('migrate:fresh --seed');
    dump('Migration done successfully');
});

Route::get('/arukereso_products', function () {

    dump("Products without category");
    $xmlFilePath = storage_path('products_arukereso.xml');
    // Read XML file using simplexml_load_file
    $xmlData = simplexml_load_file($xmlFilePath);

    // Alternatively, you can use the XML facade in Laravel
    // $xmlData = XML::load($xmlFilePath);

    // Access XML data as an object
    $count = 1;
    foreach ($xmlData as $item) {
        if (!$item->Category) {


            dump(sprintf("%d %s %s", $count, $item->Identifier, $item->Category));
        }
        $count++;
    }

});

Route::get('/test_notification', function () {
    $result = "This is just test page" . time();
    echo $result;
    $data_array['to'] = "naturprime_vcc";
    $data_array['msg'] = $result;
    Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data_array));

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
    Route::post('bulk/update', 'bulk_update_status')->name('status.bulk.update');
});

Route::get('/chart', function () {
    return view('chart');
})->name('chart');


/**
 * Create public price list
 */
Route::controller(WebshopPriceController::class)->group(function () {
    Route::get('product-list', 'index'); //get products from Leadvertex
    Route::get('products', 'get_xml');
    Route::get('products/arukereso', 'get_xml_for_arukereso');
});

Route::controller(ZappierController::class)->group(function () {
    Route::post('zapier_fb_lead', 'store');
});

Route::controller(FacebookWebhookController::class)->group(function () {
    Route::get('/facebook/webhook', 'verify');
    Route::post('/facebook/webhook', 'handleWebhook');
});

Route::get('/get-ip', function () {
    try {
        $ch = curl_init('https://icanhazip.com');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ip = trim(curl_exec($ch));
        curl_close($ch);

        return response()->json(['ip' => $ip]);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
    }
});

Route::controller(DeliveoController::class)->group(function () {
    Route::post('deliveo/webhook', 'webhook');
});

Route::controller(SzamlaController::class)->group(function () {
    Route::get('szamla', 'create_invoice');
});


Route::group(['middleware' => ['admin']], function () {
    Route::resource('users', UsersController::class);

    Route::patch('/tasks/{task}/assignee', [TaskController::class, 'updateAssignee'])->name('tasks.updateAssignee'); // Update task status by user
    Route::resource('tasks', TaskController::class)->only(['store', 'destroy', 'update']);

    /**
     * Activity Log
     */

    Route::get('activity/log', [ActivityController::class, 'index'])->name('activity.index');
    Route::get('activity/log/{user}/details', [ActivityController::class, 'details']);
});

Route::group(['middleware' => ['auth']], function () {

    Route::group([
        'prefix' => 'profile',
    ], function () {
        Route::get('/{tab?}', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('account', [ProfileController::class, 'account'])->name('profile.account');
    });

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::resource('users', UsersController::class);
    Route::post('password/{user}', [UsersController::class, 'password_update'])->name('user.password_update');

    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus'); // Update task status by user
    Route::resource('tasks', TaskController::class)->except(['store', 'destroy', 'update']);
    Route::resource('doctor-visits', DoctorVisitController::class);

});

Route::impersonate();

// Type
// 1_ Giovenrment
// 2_ Private

// Potential:
// 1_ Low
// 2_Medium
// 3_High

// Status:
// 1_Open
