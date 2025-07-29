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
        Schema::create('reporte_generados', function (Blueprint $table) {
        $table->id();
        $table->string('nombre_reporte'); // Ej: usuarios
        $table->string('formato'); // Ej: xlsx o pdf
        $table->date('rango_desde')->nullable(); // Opcional
        $table->date('rango_hasta')->nullable(); // Opcional
        $table->unsignedBigInteger('user_id')->nullable(); // Opcional si es automático
        $table->timestamp('fecha_generacion');
        $table->string('path_archivo');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reporte_generados');
    }
};
