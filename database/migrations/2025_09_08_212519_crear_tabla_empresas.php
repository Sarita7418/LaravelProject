<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id(); // PK: id
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('nit', 20)->unique();
            $table->string('matricula_comercio', 50)->nullable();
            $table->string('direccion_fiscal');
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('municipio', 120)->nullable();
            $table->string('departamento', 120)->nullable();
            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('empresas');
    }
};

