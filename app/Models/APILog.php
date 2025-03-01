<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class APILog extends Model
{
    protected $table = 'api_logs';
    protected $fillable = [
        'api_name',
        'request_body',
        'response_body',
    ];

    protected $casts = [
        'request_body' => 'array',
        'response_body' => 'array',
    ];
}
