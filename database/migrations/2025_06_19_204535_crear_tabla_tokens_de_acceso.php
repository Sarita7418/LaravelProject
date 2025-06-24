<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('tokens_de_acceso', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenizable'); // Adaptado de 'tokenable'
            $table->string('nombre');
            $table->string('token', 64)->unique();
            $table->text('permisos')->nullable(); // Adaptado de 'abilities'
            $table->timestamp('ultimo_uso_en')->nullable();
            $table->timestamp('expira_en')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_de_acceso');
    }
};
