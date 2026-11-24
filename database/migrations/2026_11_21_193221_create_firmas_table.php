<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('imagen', 100);              // ruta del PNG en storage
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->dateTime('fecha_firma');
            $table->string('metodo', 20)->default('huella');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmas');
    }
};
