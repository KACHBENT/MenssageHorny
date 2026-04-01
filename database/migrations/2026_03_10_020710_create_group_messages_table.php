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
        Schema::create('group_messages', function (Blueprint $table) {

            $table->id();

            // ID del grupo al que pertenece el mensaje
            $table->unsignedBigInteger('group_id');

            // Usuario que envía el mensaje
            $table->unsignedBigInteger('user_id');

            // Contenido del mensaje
            $table->text('message')->nullable();

            // Tipo de mensaje
            $table->enum('type', ['text', 'image', 'video'])->default('text');

            // Ruta del archivo si es imagen o video
            $table->string('file_path')->nullable();

            $table->timestamps();

            // Relaciones
            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Índices para mejorar rendimiento
            $table->index('group_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_messages');
    }
};