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
        Schema::create('stock_actual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos')->onDelete('restrict');
            $table->foreignId('id_lote')->constrained('lotes')->onDelete('restrict');
            $table->foreignId('id_ubicacion')->constrained('politicos_ubicacion')->onDelete('restrict');
            $table->integer('cantidad');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Índices únicos y de búsqueda
            $table->unique(['id_producto', 'id_lote', 'id_ubicacion'], 'unique_stock');
            $table->index(['id_producto', 'id_ubicacion']);
            $table->index('id_lote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_actual');
    }
};