<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('usuario_id')->nullable()->index();
            $table->string('ip', 45)->nullable();
            $table->text('navegador')->nullable();
            $table->longText('datos');
            $table->integer('ultima_actividad')->index();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');

        });
    }

    

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};

