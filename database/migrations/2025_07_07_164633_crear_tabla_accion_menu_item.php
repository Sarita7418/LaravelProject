<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accion_menu_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_accion')
                  ->constrained('acciones')
                  ->onDelete('cascade');

            $table->foreignId('id_menu_item')
                  ->constrained('menu_items')
                  ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['id_accion', 'id_menu_item']); // Para evitar duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accion_menu_item');
    }
};
