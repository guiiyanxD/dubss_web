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
        Schema::create('convocatoria_requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->foreignId('requisito_id')->constrained('requisitos')->restrictOnDelete();
            $table->boolean('es_obligatorio')->default(true);
            $table->integer('orden');
            $table->text('instrucciones_especificas')->nullable();
            $table->timestamps();

            $table->unique(['convocatoria_id', 'requisito_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatoria_requisitos');
    }
};
