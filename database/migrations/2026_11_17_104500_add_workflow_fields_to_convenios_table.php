<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->foreignId('unidad_responsable_id')
                ->nullable()
                ->after('tipo_instrumento_id')
                ->constrained('unidad_responsables')
                ->nullOnDelete();
            $table->foreignId('revisor_id')
                ->nullable()
                ->after('unidad_responsable_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('titulo', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->dropForeign(['unidad_responsable_id']);
            $table->dropForeign(['revisor_id']);
            $table->dropColumn(['unidad_responsable_id', 'revisor_id']);
            $table->string('titulo', 45)->change();
        });
    }
};
