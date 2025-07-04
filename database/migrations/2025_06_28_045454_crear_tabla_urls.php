<?php

// database/migrations/xxxx_xx_xx_xxxxxx_crear_tabla_urls.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       Schema::create('urls', function (Blueprint $table) {
    $table->id();
    $table->string('ruta')->unique(); // Ej: /admin/usuarios
    $table->string('componente')->nullable(); // Ya no uses "->after()"
    $table->string('tipo')->default('frontend'); // frontend o backend (opcional)
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('urls');
    }
};

