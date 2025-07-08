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
        Schema::create('versiones_protocolos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_protocolo');
            $table->integer('numero_version');
            $table->date('fecha_modificacion');
            $table->text('observaciones');
            $table->timestamps();
        
            $table->foreign('id_protocolo')->references('id')->on('protocolos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versiones_protocolos');
    }
};
