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
         Schema::create('instituto_formacion', function (Blueprint $table) {
            $table->id('id_inst_for');
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->unsignedBigInteger('id_ciudad'); // FK a politicos_ubicacion
            $table->timestamps();

            $table->foreign('id_ciudad')->references('id')->on('politicos_ubicacion')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('instituto_formacion');
    }
};
