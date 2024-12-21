<?php

namespace App\Models;

use App\Traits\ActivityLoggerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorVisit extends Model
{
    use HasFactory;
    use ActivityLoggerTrait;

    protected $fillable = [
        'hospital',         // Korhaz
        'type',             // Típus
        'potential',        // Potenciál
        'status',           // Státusz
        'chain',            // Lánc
        'address',          // Cím
        'city',             // Város
        'contact_person',   // Kontakt személy
        'contact_position', // Kontakt pozíció
        'phone_number',     // Telefonszám
        'email',            // Email
        'responsible',      // Felelős
        'visits',           // Látogatások
    ];
}
