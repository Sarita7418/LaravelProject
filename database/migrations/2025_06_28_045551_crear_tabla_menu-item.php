<?php

// database/migrations/xxxx_xx_xx_xxxxxx_crear_tabla_menu_items.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_padre')->nullable()->constrained('menu_items')->onDelete('cascade');
            $table->unsignedTinyInteger('nivel'); // 1, 2, 3...
            $table->string('item'); // Nombre del módulo, submódulo, acción
            $table->foreignId('id_url')->nullable()->constrained('urls')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('menu_items');
    }
};
