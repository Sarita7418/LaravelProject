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
        Schema::create('movimientos_presupuestarios', function (Blueprint $table) {
            $table->id();
            $table->string('comprometido', 50);
            $table->string('devengado', 50);
            $table->string('pago', 50);
            $table->string('consumido', 50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_presupuestarios');
    }
};