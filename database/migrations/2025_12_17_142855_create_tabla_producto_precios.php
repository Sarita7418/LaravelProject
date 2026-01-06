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
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos')->onDelete('cascade');
            $table->foreignId('id_tipo_precio')->constrained('subdominios'); // Referencia al subdominio ID 33 o 34 (ENTRADA/SALIDA)
            $table->decimal('precio', 10, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para búsquedas rápidas
            $table->index(['id_producto', 'id_tipo_precio', 'activo']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_precios');
    }
};