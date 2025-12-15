<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secuencias_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->integer('anio');
            $table->unsignedInteger('ultimo')->default(0);
            $table->timestamps();

            $table->unique(['tipo','anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secuencias_comprobantes');
    }
};
