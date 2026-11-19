<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->foreignId('unidad_responsable_secundaria_id')
                ->nullable()
                ->after('unidad_responsable_id')
                ->constrained('unidad_responsables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('convenios', function (Blueprint $table) {
            $table->dropForeign(['unidad_responsable_secundaria_id']);
            $table->dropColumn('unidad_responsable_secundaria_id');
        });
    }
};
