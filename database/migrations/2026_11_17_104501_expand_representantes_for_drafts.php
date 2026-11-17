<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('representantes', function (Blueprint $table) {
            $table->string('apellido', 45)->nullable()->after('nombre');
            $table->string('cargo', 45)->nullable()->after('apellido');
            $table->string('dni', 45)->nullable()->after('cargo');
            $table->string('nombre', 45)->nullable()->change();
            $table->string('email', 45)->nullable()->change();
            $table->string('cuil', 45)->nullable()->change();
            $table->string('telefono', 45)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('representantes', function (Blueprint $table) {
            $table->dropColumn(['apellido', 'cargo', 'dni']);
            $table->string('nombre', 45)->nullable(false)->change();
            $table->string('email', 45)->nullable(false)->change();
            $table->string('cuil', 45)->nullable(false)->change();
            $table->string('telefono', 45)->nullable(false)->change();
        });
    }
};
