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
        Schema::create('plan_cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('descripcion', 255);
            $table->foreignId('tipo')->constrained('subdominios'); // Referencia al subdominio ID 4 o 5
            $table->tinyInteger('nivel');
            $table->foreignId('grupo_estado_financiero')->constrained('subdominios'); // Referencia al subdominio ID 6-10
            $table->foreignId('id_padre')->nullable()->constrained('plan_cuentas')->onDelete('cascade');
            $table->foreignId('cuenta_ajuste')->constrained('subdominios'); // Referencia al subdominio ID 11 o 12
            $table->foreignId('cuenta_presupuesto')->constrained('subdominios'); // Referencia al subdominio ID 13 o 14
            $table->boolean('estado')->default(true);
            $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_cuentas');
    }
};