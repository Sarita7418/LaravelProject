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
        Schema::create('importacion_liname_raws', function (Blueprint $table) {
            $table->id();
            // Estos nombres deben coincidir con tus columnas del Excel (en minúscula)
            $table->string('codigo_completo')->nullable();
            $table->string('grupo_co')->nullable();
            $table->string('subgrupo_di')->nullable();
            $table->string('correlativo_go')->nullable();
            $table->text('medicamento_nombre')->nullable();
            $table->string('forma')->nullable();
            $table->string('concentracion')->nullable();
            $table->string('codigo_atq')->nullable();
            $table->string('uso_restringido')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacion_liname_raws');
    }
};
