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
        Schema::create('tramite_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tramite_id')->constrained('tramites')->cascadeOnDelete();
            $table->foreignId('estado_anterior_id')->nullable()->constrained('flujo_estados')->nullOnDelete();
            $table->foreignId('estado_nuevo_id')->constrained('flujo_estados')->restrictOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->restrictOnDelete(); // quien hizo cambio
            $table->timestamp('fecha_cambio');
            $table->integer('tiempo_en_estado_anterior_min')->nullable();
            $table->text('comentario')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->index(['tramite_id', 'fecha_cambio']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tramite_historial');
    }
};
