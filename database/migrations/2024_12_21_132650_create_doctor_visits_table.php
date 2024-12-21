<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('doctor_visits', function (Blueprint $table) {
            $table->id();
            $table->string('hospital')->nullable(); // Korhaz
            $table->string('type')->nullable(); // Típus
            $table->string('potential')->nullable(); // Potenciál
            $table->string('status')->nullable(); // Státusz
            $table->string('chain')->nullable(); // Lánc
            $table->string('address')->nullable(); // Cím
            $table->string('city')->nullable(); // Város
            $table->string('contact_person')->nullable(); // Kontakt személy
            $table->string('contact_position')->nullable(); // Kontakt pozíció
            $table->string('phone_number')->nullable(); // Telefonszám
            $table->string('email')->nullable(); // Email
            $table->string('responsible')->nullable(); // Felelős
            $table->text('visits')->nullable(); // Látogatások
            $table->timestamps(); // Created at & Updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_visits');
    }
};
