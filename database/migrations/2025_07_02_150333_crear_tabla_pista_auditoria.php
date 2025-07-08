<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pista_auditoria', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('id_usuario');
            $table->string('accion', 150);
            $table->string('tabla_afectada', 100); 
            $table->string('registro_id', 100);
            $table->string('descripcion', 500)->nullable();
            $table->dateTime('fecha_hora')->useCurrent(); 
            //$table->string('usuario_bd', 30);
            //$table->string('nombre_tabla', 50);
            //$table->string('codigo_usuario', 10)->nullable();
            //$table->string('codigo_regional_usuario', 10)->nullable();
            //$table->longText('registros1')->nullable(); 
            //$table->longText('registros2')->nullable(); 
            //$table->timestamps(); 

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('pista_auditoria');
    }
};
