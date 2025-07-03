<?php
// database/migrations/xxxx_xx_xx_xxxxxx_crear_tabla_permisos.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permisos', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('id_menu_item');
    $table->timestamps();

    $table->foreign('id_menu_item')->references('id')->on('menu_items')->onDelete('cascade');
});

    }

    public function down(): void {
        Schema::dropIfExists('permisos');
    }
};
