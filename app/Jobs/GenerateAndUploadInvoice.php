<?php

namespace App\Jobs;

use App\Http\Controllers\SalesRenderController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateAndUploadInvoice implements ShouldQueue
{
    use Queueable;

    protected $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        $controller = new SalesRenderController();
        Log::info('Queue job is working for order: ' . $this->orderId);
        $controller->create_invoice($this->orderId);
    }
}
