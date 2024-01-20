<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'leadvertex/webhook',
        'leadvertex-all-orders/webhook',
        'orders',
        'naturprime_leadvertex_new_order/webhook',
        'zapier_fb_lead',
        '/facebook/webhook'
    ];
}