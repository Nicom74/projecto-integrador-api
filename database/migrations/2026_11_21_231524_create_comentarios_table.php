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
        Schema::create('comentarios', function (Blueprint $table) {
        $table->id();
        $table->foreignId('convenio_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users');
        $table->string('texto', 500);
        $table->boolean('ofensivo')->default(false);
        $table->timestamps();
        $table->index(['convenio_id', 'id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
