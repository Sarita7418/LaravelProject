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
        Schema::create('plan_presupuestarios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('descripcion', 255);
            $table->foreignId('tipo')->constrained('subdominios'); // Referencia al subdominio ID 4 o 5
            $table->tinyInteger('nivel');
            $table->foreignId('id_padre')->nullable()->constrained('plan_presupuestarios')->onDelete('cascade');
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_presupuestarios');
    }
};