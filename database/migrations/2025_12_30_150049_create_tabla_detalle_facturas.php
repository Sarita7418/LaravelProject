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
    Schema::create('detalle_facturas', function (Blueprint $table) {
        $table->id();
        
        // Si borras la factura, se borran sus detalles (cascade)
        $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
        $table->foreignId('producto_id')->constrained('productos');
        
        $table->integer('cantidad');
        $table->decimal('precio_unitario', 10, 2); // Precio al momento de la venta
        $table->decimal('subtotal', 10, 2);
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabla_detalle_facturas');
    }
};
