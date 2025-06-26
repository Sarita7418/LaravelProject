<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('pista_auditorias', function (Blueprint $table) {
            $table->id('id_p_auditoria');
            $table->timestamp('fecha_hora')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('ip_maquina')->nullable();
            $table->string('nombre_maquina')->nullable();
            $table->string('tabla_afectada');
            $table->string('accion_realizada');
            $table->longText('info_antes')->nullable();
            $table->longText('info_despues')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('set null');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pista_auditorias');
    }
};
