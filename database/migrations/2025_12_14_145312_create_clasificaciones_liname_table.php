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
        Schema::create('clasificaciones_liname', function (Blueprint $table) {
            $table->id();
            $table->integer('nivel'); // 1 para Grupos, 2 para Subgrupos
            $table->string('codigo', 5); // 'A', '01', etc.
            $table->string('nombre'); // Descripción larga
            // Clave foránea recursiva (apunta a esta misma tabla)
            $table->foreignId('padre_id')
                ->nullable()
                ->constrained('clasificaciones_liname')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clasificaciones_liname');
    }
};
