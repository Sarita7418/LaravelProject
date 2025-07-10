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
        Schema::create('relaciones_personales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_persona_base')->constrained('personas')->onDelete('cascade');
            $table->foreignId('id_familiar')->constrained('personas')->onDelete('cascade');
            $table->string('parentesco');
            $table->tinyInteger('nivel'); // 1, 2, 3, 4...
            $table->timestamps();

            $table->unique(['id_persona_base', 'id_familiar']); // Para evitar duplicados
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
