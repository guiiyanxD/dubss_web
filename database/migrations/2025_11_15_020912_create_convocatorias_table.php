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
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo')->unique();
            $table->enum('tipo_beca', [
                'socioeconomica',
                'academica',
                'extension'
            ]);
            $table->string('subtipo'); // estudio, albergue, alimentacion, internado_rotatorio, investigacion, excelencia
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_limite_apelacion');
            $table->boolean('activa')->default(true);
            $table->string('version')->default('1.0.0');
            $table->timestamps();

            $table->index(['activa', 'fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
