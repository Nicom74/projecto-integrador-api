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
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_creacion');
            $table->string('firma_gestor', 45)->nullable();
            $table->string('firma_decano', 45)->nullable();
            $table->string('titulo', 45);
            $table->string('estado', 45);
            $table->string('codigo', 45)->unique();
            $table->foreignId('convenio_padre_id')->nullable()->constrained('convenios');
            $table->foreignId('tipo_instrumento_id')->constrained('tipo_instrumentos');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
