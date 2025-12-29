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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha');
            $table->foreignId('id_tipo_movimiento')->constrained('subdominios'); // Referencia al subdominio ID 30, 31 o 32 (COMPRA/SALIDA/AJUSTE)
            $table->string('referencia', 100)->nullable();
            $table->foreignId('id_producto')->constrained('productos')->onDelete('restrict');
            $table->foreignId('id_lote')->constrained('lotes')->onDelete('restrict');
            $table->integer('cantidad_entrada')->default(0);
            $table->integer('cantidad_salida')->default(0);
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('costo_total', 10, 2);
            $table->foreignId('id_ubicacion_origen')->nullable()->constrained('politicos_ubicacion')->onDelete('restrict');
            $table->foreignId('id_ubicacion_destino')->nullable()->constrained('politicos_ubicacion')->onDelete('restrict');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('restrict');
            $table->timestamp('created_at')->useCurrent();

            // Índices para búsquedas rápidas
            $table->index(['id_producto', 'fecha']);
            $table->index(['id_lote', 'fecha']);
            $table->index(['id_tipo_movimiento', 'fecha']);
            $table->index('id_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};