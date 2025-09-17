<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logos', function (Blueprint $table) {
            $table->id();
            // id_entidad puede referirse a cualquier entidad (empresa o sucursal)
            $table->unsignedBigInteger('id_entidad');
            $table->string('tipo_entidad');  // 'empresa' o 'sucursal'
            $table->binary('logo');  // Logo almacenado en formato binario
            $table->timestamps();
        });
    }


    public function down(): void
    {

    }
};
