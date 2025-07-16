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
        Schema::create('area_impacto_protocolo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_area_impactos')->constrained('area_impactos')->onDelete('cascade');
            $table->foreignId('id_protocolo')->constrained('protocolos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('area_impacto_protocolo');
    }
};
