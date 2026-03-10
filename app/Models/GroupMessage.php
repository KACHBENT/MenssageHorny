<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Group;

class GroupMessage extends Model
{
    use HasFactory;

    protected $table = 'group_messages';

    protected $fillable = [
        'group_id',
        'user_id',
        'message',
        'type',
        'file_path'
    ];

    /**
     * Grupo al que pertenece el mensaje
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Usuario que envió el mensaje
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}