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
        Schema::create('detalle_pasantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('persona_vinculadas');
            $table->foreignId('tutor_academico_id')->constrained('persona_vinculadas');
            $table->foreignId('tutor_guia_id')->constrained('persona_vinculadas');
            $table->string('carrera', 45);
            $table->string('carga_horaria_semanal', 45);
            $table->string('asignacion_estimulo', 45);
            $table->string('cobertura_seguro_art', 45);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pasantias');
    }
};
