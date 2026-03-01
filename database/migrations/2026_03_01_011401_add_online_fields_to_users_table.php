<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOnlineFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Verificar si la columna 'is_online' NO existe antes de agregarla
            if (!Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false);
            }
            
            // Verificar si la columna 'last_seen' NO existe antes de agregarla
            if (!Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo intentar eliminar las columnas si existen
            if (Schema::hasColumn('users', 'is_online')) {
                $table->dropColumn('is_online');
            }
            
            if (Schema::hasColumn('users', 'last_seen')) {
                $table->dropColumn('last_seen');
            }
        });
    }
}