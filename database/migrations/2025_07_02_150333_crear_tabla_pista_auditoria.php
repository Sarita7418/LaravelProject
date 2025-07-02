<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pista_auditoria', function (Blueprint $table) {
            $table->id(); 
            $table->timestamp('fecha')->useCurrent(); 
            $table->string('usuario_bd', 30); 
            $table->string('accion', 20);
            $table->string('nombre_host', 30); 
            $table->string('ip_host', 30); 
            $table->string('pk', 500)->nullable();
            $table->string('nombre_tabla', 50);
            //$table->string('codigo_usuario', 10)->nullable();
            //$table->string('codigo_regional_usuario', 10)->nullable();
            $table->longText('registros1')->nullable(); 
            $table->longText('registros2')->nullable(); 
            $table->timestamps(); 
        });
    }

    public function down(): void {
        Schema::dropIfExists('pista_auditoria');
    }
};
