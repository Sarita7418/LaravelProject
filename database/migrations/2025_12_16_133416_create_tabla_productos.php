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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo_interno', 50)->unique()->nullable();
            $table->string('codigo_barras', 50)->nullable();
            $table->foreignId('id_categoria')->constrained('subdominios'); // Referencia al subdominio ID 13 o 14 (BIEN/SERVICIO)
            $table->boolean('rastrea_inventario')->default(true);
            $table->foreignId('id_unidad_medida')->constrained('subdominios'); // Referencia al subdominio ID 15-21
            $table->decimal('precio_entrada', 10, 2)->default(0.00);
            $table->decimal('precio_salida', 10, 2)->default(0.00);
            $table->integer('stock_minimo')->default(0);
            $table->foreignId('id_estado_producto')->constrained('subdominios'); // Referencia al subdominio ID 22 o 23 (ACTIVO/INACTIVO)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};