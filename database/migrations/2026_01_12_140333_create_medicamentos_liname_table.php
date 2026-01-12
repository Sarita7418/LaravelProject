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
        Schema::create('medicamentos_liname', function (Blueprint $table) {
            $table->id();

            // Relación 1: ¿A qué grupo pertenece? (Ej: A01)
            $table->foreignId('clasificacion_id')->constrained('clasificaciones_liname');

            // Relación 2: ¿Qué droga es? (Ej: Ácido Ascórbico)
            $table->foreignId('producto_generico_id')->constrained('productos_genericos');

            // Datos específicos de esta presentación
            $table->string('correlativo_go', 5); // Ej: 02
            $table->string('codigo_completo')->unique(); // Ej: A1102 (Útil para buscar)
            $table->string('forma_farmaceutica'); // Ej: Inyectable
            $table->string('concentracion'); // Ej: 500 mg/ml
            $table->string('uso_restringido')->nullable(); // Ej: R

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicamentos_liname');
    }
};
