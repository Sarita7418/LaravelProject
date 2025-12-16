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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empresa')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('id_sucursal')->constrained('sucursales')->onDelete('cascade');
            $table->foreignId('id_proveedor')->constrained('personas')->onDelete('restrict');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('restrict');
            $table->date('fecha_compra');
            $table->string('nro_documento', 50)->nullable();
            $table->text('observacion')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('descuento_total', 10, 2)->default(0.00);
            $table->decimal('total_compra', 10, 2)->default(0.00);
            $table->foreignId('id_estado_compra')->constrained('subdominios'); // Referencia al subdominio ID 24, 25 o 26 (BORRADOR/CONFIRMADO/ANULADO)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};