<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('facturas', function (Blueprint $table) {
        $table->id();
        
        // Relaciones
        $table->foreignId('cliente_id')->constrained('clientes'); 
        $table->foreignId('user_id')->constrained('usuarios'); // Quién hizo la venta
        
        // Datos Fiscales
        $table->bigInteger('numero_factura'); // Correlativo (1, 2, 3...)
        $table->string('cuf', 255)->nullable(); // Código Único de Facturación (Vital para Impuestos)
        
        // Totales
        $table->dateTime('fecha_emision');
        $table->decimal('monto_total', 10, 2);
        
        // Estado (VALIDA, ANULADA)
        $table->string('estado')->default('VALIDA'); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabla_facturas');
    }
};
