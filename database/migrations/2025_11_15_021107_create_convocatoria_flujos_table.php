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
        Schema::create('convocatoria_flujos', function (Blueprint $table) {
           $table->id();
            $table->foreignId('convocatoria_id')->constrained('convocatorias')->cascadeOnDelete();
            $table->foreignId('estado_id')->constrained('flujo_estados')->restrictOnDelete();
            $table->foreignId('estado_siguiente_id')->nullable()->constrained('flujo_estados')->nullOnDelete();
            $table->integer('orden');
            $table->integer('tiempo_estimado_horas')->default(24);
            $table->boolean('requiere_notificacion')->default(true);
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->unique(['convocatoria_id', 'estado_id']);
            $table->index(['convocatoria_id', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convocatoria_flujos');
    }
};
