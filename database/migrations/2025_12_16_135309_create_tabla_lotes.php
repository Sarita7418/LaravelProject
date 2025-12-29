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
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_compra')->constrained('compras')->onDelete('restrict');
            $table->foreignId('id_producto')->constrained('productos')->onDelete('restrict');
            $table->string('numero_lote', 50);
            $table->integer('cantidad_inicial');
            $table->date('fecha_ingreso');
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('id_estado_lote')->constrained('subdominios'); // Referencia al subdominio ID 27, 28 o 29 (ACTIVO/AGOTADO/DAÑADO)
            $table->timestamps();

            // Índice compuesto para búsquedas rápidas
            $table->index(['id_producto', 'numero_lote']);
            $table->index(['id_estado_lote', 'fecha_vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};