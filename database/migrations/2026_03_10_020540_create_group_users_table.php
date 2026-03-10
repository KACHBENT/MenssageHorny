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
        Schema::create('group_users', function (Blueprint $table) {

            $table->id();

            // ID del grupo
            $table->unsignedBigInteger('group_id');

            // ID del usuario que pertenece al grupo
            $table->unsignedBigInteger('user_id');

            // Rol del usuario dentro del grupo
            $table->enum('role', ['admin', 'member'])->default('member');

            $table->timestamps();

            // Relación con la tabla groups
            $table->foreign('group_id')
                ->references('id')
                ->on('groups')
                ->onDelete('cascade');

            // Relación con la tabla users
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Evita duplicar usuario en el mismo grupo
            $table->unique(['group_id', 'user_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_users');
    }
};