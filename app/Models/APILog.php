<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class APILog extends Model
{
    protected $table = 'api_logs';
    protected $fillable = [
        'order_id',
        'crm',
        'api_url',
        'request_method',
        'request_body',
        'response_body',
        'status',
    ];

    protected $casts = [
        'request_body' => 'array',
        'response_body' => 'array',
    ];
}
