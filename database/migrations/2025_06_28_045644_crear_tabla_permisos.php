<?php
// database/migrations/xxxx_xx_xx_xxxxxx_crear_tabla_permisos.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_menu_item')->constrained('menu_items')->onDelete('cascade');
            $table->foreignId('id_rol')->constrained('roles')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['id_menu_item', 'id_rol']); // No se repiten permisos duplicados
        });
    }

    public function down(): void {
        Schema::dropIfExists('permisos');
    }
};
