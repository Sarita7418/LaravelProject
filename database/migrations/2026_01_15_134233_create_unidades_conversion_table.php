<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('unidades_conversion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_producto')->constrained('productos');

            $table->foreignId('unidad_origen')->constrained('subdominios');
            $table->foreignId('unidad_destino')->constrained('subdominios');

            $table->integer('factor'); // ej: 1 caja = 30 unidades

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidades_conversion');
    }
};
