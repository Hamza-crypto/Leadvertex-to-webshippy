<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_id',
        'destination_id',
        'status',
        'delivery_date',
        'last_synced_at',
        'created_at',
        'updated_at',
    ];
    //
    //
    

}
