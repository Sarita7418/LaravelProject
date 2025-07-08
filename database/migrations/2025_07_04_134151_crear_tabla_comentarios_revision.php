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
        Schema::create('comentarios_revision', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_protocolos');
            $table->unsignedBigInteger('id_usuario');
            $table->text('comentario');
            $table->date('fecha');
            $table->timestamps();
        
            $table->foreign('id_protocolos')->references('id')->on('protocolos')->onDelete('cascade');
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios_revision');
    }
};
