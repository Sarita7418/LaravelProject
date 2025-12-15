<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobante_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comprobante_id')->constrained('comprobantes')->cascadeOnDelete();
            $table->unsignedBigInteger('cuenta_id'); // FK a plan_cuentas
            $table->text('glosa_detalle')->nullable();
            $table->decimal('debe', 15, 2)->default(0);
            $table->decimal('haber', 15, 2)->default(0);
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('cuenta_id')->references('id')->on('plan_cuentas')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobante_detalles');
    }
};
