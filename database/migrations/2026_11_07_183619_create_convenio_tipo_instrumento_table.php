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
        Schema::create('convenio_tipo_instrumento', function (Blueprint $table) {
            $table->foreignId('convenio_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tipo_instrumento_id')->constrained()->cascadeOnDelete();
            $table->primary(['convenio_id', 'tipo_instrumento_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_tipo_instrumento');
    }
};
