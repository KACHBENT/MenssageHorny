<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use App\Models\User;
use App\Models\Group;
=======
>>>>>>> main

class GroupMessage extends Model
{
    use HasFactory;

    protected $table = 'group_messages';

    protected $fillable = [
        'group_id',
        'user_id',
        'message',
        'type',
<<<<<<< HEAD
        'file_path'
    ];

    /**
     * Grupo al que pertenece el mensaje
     */
=======
        'file_path',
    ];

>>>>>>> main
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

<<<<<<< HEAD
    /**
     * Usuario que envió el mensaje
     */
=======
>>>>>>> main
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}