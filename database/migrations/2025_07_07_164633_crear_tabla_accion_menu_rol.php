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

            $table->foreignId('accion_id')
                  ->constrained('acciones')
                  ->onDelete('cascade');

            $table->foreignId('menu_item_id')
                  ->constrained('menu_items')
                  ->onDelete('cascade');

            $table->timestamps();

            $table->unique(['accion_id', 'menu_item_id']); // Para evitar duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accion_menu_item');
    }
};
