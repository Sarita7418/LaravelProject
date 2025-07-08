<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_item_rol', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_rol')
                  ->constrained('roles')
                  ->onDelete('cascade');

            $table->foreignId('id_menu_item')
                  ->constrained('menu_items')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_rol');
    }
};
