<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('productos_comerciales', function (Blueprint $table) {
        $table->id();
        
        // 1. RELACIÓN CON EL PADRE (GENÉRICO)
        // Puede ser nulo porque quizas el excel trae un producto cuyo genérico aun no registraste
        $table->foreignId('producto_generico_id')
              ->nullable()
              ->constrained('productos_genericos')
              ->onDelete('set null'); 

        // 2. DATOS DEL EXCEL (VENTALI)
        $table->string('nro_registro_sanitario')->nullable(); // Ej: 16779
        $table->string('nombre_comercial');       // Ej: COFIBRAN
        $table->string('forma_farmaceutica')->nullable(); // Ej: GRANULADO
        $table->string('concentracion')->nullable();      // Ej: 178.6G
        $table->string('laboratorio_fabricante')->nullable(); // Ej: COFAR
        $table->string('empresa_proveedora')->nullable();     // Ej: DROGUERIA INTI
        $table->string('pais_origen')->nullable();            // Ej: BOLIVIA

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_comerciales');
    }
};
