<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accion_menu_item_rol', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_rol')
                  ->constrained('roles')
                  ->onDelete('cascade');

            $table->foreignId('id_menu_item')
                  ->constrained('menu_items')
                  ->onDelete('cascade');

            $table->foreignId('id_accion')
                  ->constrained('acciones')
                  ->onDelete('cascade');

            $table->timestamps();

            // Evitar duplicados exactos
            $table->unique(['id_rol', 'id_menu_item', 'id_accion'], 'unique_accion_menu_rol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accion_menu_item_rol');
    }
};
