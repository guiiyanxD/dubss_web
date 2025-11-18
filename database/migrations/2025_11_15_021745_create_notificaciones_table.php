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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tramite_id')->nullable()->constrained('tramites')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['info', 'success', 'warning', 'error'])->default('info');
            $table->enum('canal', ['app', 'email', 'sms'])->default('app');
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();
            $table->json('metadatos')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'leida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
