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
        Schema::create('personas', function (Blueprint $table) {
        $table->id();
        $table->string('nombres');
        $table->string('apellido_paterno');
        $table->string('apellido_materno')->nullable();
        $table->string('ci')->unique();
        $table->string('telefono')->nullable();
        $table->date('fecha_nacimiento')->nullable(); 
        $table->boolean('estado')->default(1); // 1 = activo, 0 = inactivo
        $table->timestamps();
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
