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
        Schema::create('area_impacto', function (Blueprint $table) {
            $table->id();        
            $table->unsignedBigInteger('id_area_impacto');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('id_area_impacto')->references('id')->on('subdominios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_impactos');
    }
};
