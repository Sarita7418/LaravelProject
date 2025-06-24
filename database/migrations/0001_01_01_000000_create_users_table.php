<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id')->nullable();

            $table->string('nombre');
            $table->string('correo')->unique();
            $table->timestamp('correo_verificado_en')->nullable();
            $table->string('contrasena');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
