<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero');
            $table->string('tipo'); // ingreso|egreso|diario
            $table->integer('anio');
            $table->date('fecha');
            $table->text('glosa_general')->nullable();
            $table->decimal('total_debe', 15, 2)->default(0);
            $table->decimal('total_haber', 15, 2)->default(0);
            $table->text('monto_letras')->nullable();
            $table->string('elaborado_por')->nullable();
            $table->string('aprobado_por')->nullable();
            $table->string('verificado_por')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['tipo','anio','numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
