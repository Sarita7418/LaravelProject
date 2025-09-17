<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sucursales', function (Blueprint $table) {
            $table->id(); // PK: id

            // FK personalizada: id_empresa -> empresas.id
            $table->foreignId('id_empresa')
                  ->constrained(table: 'empresas', column: 'id')
                  ->cascadeOnDelete();

            $table->string('nombre');
            $table->string('codigo_sucursal')->default(0); // 0 = Casa Matriz (SIN)
            $table->string('direccion');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('sucursales');
    }
};

