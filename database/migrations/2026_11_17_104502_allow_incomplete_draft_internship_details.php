<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pasantias', function (Blueprint $table) {
            $table->foreignId('estudiante_id')->nullable()->change();
            $table->foreignId('tutor_academico_id')->nullable()->change();
            $table->foreignId('tutor_guia_id')->nullable()->change();
            $table->string('carrera', 45)->nullable()->change();
            $table->string('carga_horaria_semanal', 45)->nullable()->change();
            $table->string('asignacion_estimulo', 45)->nullable()->change();
            $table->string('cobertura_seguro_art', 45)->nullable()->change();
            $table->date('fecha_inicio')->nullable()->change();
            $table->date('fecha_fin')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pasantias', function (Blueprint $table) {
            $table->foreignId('estudiante_id')->nullable(false)->change();
            $table->foreignId('tutor_academico_id')->nullable(false)->change();
            $table->foreignId('tutor_guia_id')->nullable(false)->change();
            $table->string('carrera', 45)->nullable(false)->change();
            $table->string('carga_horaria_semanal', 45)->nullable(false)->change();
            $table->string('asignacion_estimulo', 45)->nullable(false)->change();
            $table->string('cobertura_seguro_art', 45)->nullable(false)->change();
            $table->date('fecha_inicio')->nullable(false)->change();
            $table->date('fecha_fin')->nullable(false)->change();
        });
    }
};
