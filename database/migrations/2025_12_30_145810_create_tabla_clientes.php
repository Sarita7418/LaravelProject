<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('clientes', function (Blueprint $table) {
        $table->id();
        $table->string('razon_social'); // Nombre o Empresa (Ej: Juan Perez o Embol S.A.)
        $table->string('nit_ci', 20);   // El número de documento
        $table->string('complemento', 5)->nullable(); // Opcional para CIs duplicados
        $table->string('email')->nullable(); // Clave para enviar la factura digital
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabla_clientes');
    }
};
