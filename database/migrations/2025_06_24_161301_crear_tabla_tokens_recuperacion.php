<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tokens_recuperacion', function (Blueprint $table) {
            $table->string('correo')->primary();
            $table->string('token');
            $table->timestamp('creado_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_recuperacion');
    }
};
