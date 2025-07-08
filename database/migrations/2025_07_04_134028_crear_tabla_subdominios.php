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
        Schema::create('subdominios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_dominio');
            $table->string('descripcion', 150);
            $table->timestamps();
        
            $table->foreign('id_dominio')->references('id')->on('dominios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subdominios');
    }
};
