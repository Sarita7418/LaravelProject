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
        Schema::create('documentos_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_protocolo');
            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 300);
            $table->string('tipo_documento', 50);
            $table->timestamps();
        
            $table->foreign('id_protocolo')->references('id')->on('protocolos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_adjuntos');
    }
};
