<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\GroupMessage;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'creator_id',
        'image',
        'description'
    ];

<<<<<<< HEAD
    /**
     * Usuario que creó el grupo
     */
=======
>>>>>>> main
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

<<<<<<< HEAD
    /**
     * Usuarios que pertenecen al grupo
     */
=======
>>>>>>> main
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'group_users',
            'group_id',
            'user_id'
<<<<<<< HEAD
        )->withTimestamps();
    }

    /**
     * Mensajes del grupo
     */
=======
        )->withPivot('role')->withTimestamps();
    }

>>>>>>> main
    public function messages()
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }
<<<<<<< HEAD
=======

    public function lastMessage()
    {
        return $this->hasOne(GroupMessage::class, 'group_id')->latestOfMany();
    }
>>>>>>> main
}