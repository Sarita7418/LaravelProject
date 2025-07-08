<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('item', 100);                  // Nombre del ítem (ej. Usuarios)
            $table->string('ruta', 100);                  // Ruta asociada (ej. /usuarios)
            $table->foreignId('id_padre')                 // Menú padre si es submenú
                  ->nullable()
                  ->constrained('menu_items')
                  ->onDelete('set null');
            $table->integer('nivel')->default(1);         // Nivel jerárquico (1 = principal)
            $table->integer('orden')->default(0);         // Para ordenar el menú en la vista
            $table->timestamps();                         // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
