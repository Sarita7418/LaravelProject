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
        Schema::create('educacion', function (Blueprint $table) {
            $table->id('id_educacion');
            $table->date('fecha_int_prof')->nullable();
            $table->date('fecha_titulacion');
            $table->unsignedBigInteger('id_persona');
            $table->unsignedBigInteger('id_inst_for');
            $table->unsignedBigInteger('id_ocupacion');
            $table->unsignedBigInteger('id_tipoGrado');
            $table->timestamps();

            $table->foreign('id_persona')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('id_inst_for')->references('id_inst_for')->on('instituto_formacion')->onDelete('cascade');
            $table->foreign('id_ocupacion')->references('id')->on('subdominios')->onDelete('cascade');
            $table->foreign('id_tipoGrado')->references('id')->on('subdominios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('educacion');
    }
};
