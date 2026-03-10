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
        Schema::create('groups', function (Blueprint $table) {

            $table->id();

            // Nombre del grupo
            $table->string('name');

            // Usuario que creó el grupo
            $table->unsignedBigInteger('creator_id');

            // Imagen opcional del grupo
            $table->string('image')->nullable();

            // Descripción del grupo (opcional)
            $table->text('description')->nullable();

            $table->timestamps();

            // Relación con usuarios
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};