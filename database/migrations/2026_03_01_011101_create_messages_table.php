<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id'); // Usuario que envía el mensaje
            $table->text('message')->nullable(); // Mensaje encriptado
            $table->enum('type', ['text', 'image', 'video'])->default('text');
            $table->string('file_path')->nullable(); // Ruta del archivo si es imagen/video
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Llaves foráneas
            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('conversations')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Índices para búsquedas más rápidas
            $table->index('conversation_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}