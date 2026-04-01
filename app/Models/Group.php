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

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'group_users',
            'group_id',
            'user_id'
        )->withPivot('role')->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class, 'group_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(GroupMessage::class, 'group_id')->latestOfMany();
    }
}