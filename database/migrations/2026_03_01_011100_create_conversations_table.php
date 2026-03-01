<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_one');
            $table->unsignedBigInteger('user_two');
            $table->timestamps();
            
            // Llaves foráneas
            $table->foreign('user_one')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('user_two')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Evitar conversaciones duplicadas entre los mismos usuarios
            $table->unique(['user_one', 'user_two']);
            
            // Índices para búsquedas más rápidas
            $table->index('user_one');
            $table->index('user_two');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}