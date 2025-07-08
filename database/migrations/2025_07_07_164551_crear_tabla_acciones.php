<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique(); // Ej: crear_usuarios
            $table->text('descripcion')->nullable(); // Descripción opcional de la acción
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones');
    }
};
