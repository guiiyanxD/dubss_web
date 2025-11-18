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
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->restrictOnDelete();
            $table->foreignId('estado_actual_id')->constrained('flujo_estados')->restrictOnDelete();
            $table->foreignId('turno_id')->nullable()->constrained('turnos')->nullOnDelete();
            $table->foreignId('operador_asignado_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_solicitud');
            $table->timestamp('fecha_ultima_actualizacion');
            $table->boolean('es_apelacion')->default(false);
            $table->foreignId('tramite_original_id')->nullable()->constrained('tramites')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['usuario_id', 'estado_actual_id']);
            $table->index(['convocatoria_id', 'estado_actual_id']);
            $table->index('codigo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tramites');
    }
};
