<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo_interno', 50)->unique();

            $table->foreignId('id_medicamento_liname')
                  ->nullable()
                  ->constrained('medicamentos_liname');

            $table->foreignId('id_unidad_venta') // blister, tableta, unidad
                  ->constrained('subdominios');

            $table->foreignId('id_categoria')->constrained('subdominios'); // bien/servicio
            $table->boolean('rastrea_inventario')->default(true);

            $table->foreignId('id_unidad_medida') 
                  ->constrained('subdominios');

            $table->integer('unidades_empaque'); 

            $table->decimal('precio_entrada', 10, 2);
            $table->decimal('precio_salida', 10, 2);

            $table->integer('stock_minimo')->default(0);

            $table->foreignId('id_estado_producto')->constrained('subdominios');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
