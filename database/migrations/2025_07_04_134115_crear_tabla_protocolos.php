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
        Schema::create('protocolos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario_creador');
            $table->unsignedBigInteger('id_especialidad');
            $table->unsignedBigInteger('id_estado');
            $table->string('titulo', 200);
            $table->text('resumen');
            $table->text('objetivo_general');
            $table->text('metodologia');
            $table->text('justificacion');
            $table->date('fecha_creacion');
            $table->unsignedBigInteger('id_area_impacto');
            $table->timestamps();
        
            $table->foreign('id_usuario_creador')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('id_especialidad')->references('id')->on('especialidades')->onDelete('cascade');
            $table->foreign('id_estado')->references('id')->on('subdominios')->onDelete('cascade');
            $table->foreign('id_area_impacto')->references('id')->on('subdominios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocolos');
    }
};
