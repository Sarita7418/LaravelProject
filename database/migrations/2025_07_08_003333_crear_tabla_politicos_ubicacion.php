<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('politicos_ubicacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_padre')->nullable();
            $table->string('tipo', 20); 
            $table->string('descripcion');
            $table->timestamps();

            $table->index('tipo');
            
            $table->foreign('id_padre')
                  ->references('id')
                  ->on('politicos_ubicacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicos_ubicacion');
    }
};