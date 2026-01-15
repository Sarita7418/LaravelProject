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
        Schema::create('productos_genericos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Ácido Ascórbico (Vitamina C)
            $table->string('codigo_atq')->nullable(); // Ej: A11GA01
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos_genericos');
    }
};
