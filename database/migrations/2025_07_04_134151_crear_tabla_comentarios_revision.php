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
            $table->unsignedBigInteger('id_version_protocolo');
            $table->unsignedBigInteger('id_usuario_revisor');
            $table->string('comentario', 500);
            $table->dateTime('fecha');
            $table->timestamps();
        
            $table->foreign('id_version_protocolo')->references('id')->on('protocolos')->onDelete('cascade');
            $table->foreign('id_usuario_revisor')->references('id')->on('usuarios')->onDelete('cascade');
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
