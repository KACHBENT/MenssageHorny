<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_one',
        'user_two',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the first user of the conversation.
     */
    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    /**
     * Get the second user of the conversation.
     */
    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    /**
     * Get all messages of the conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the last message of the conversation.
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)->latest();
    }

    /**
     * Get the other user of the conversation.
     */
    public function otherUser($currentUserId)
    {
        if ($this->user_one == $currentUserId) {
            return $this->belongsTo(User::class, 'user_two')->first();
        }
        
        return $this->belongsTo(User::class, 'user_one')->first();
    }

    /**
     * Get unread messages count for a user.
     */
    public function unreadCountForUser($userId)
    {
        return $this->messages()
                    ->where('user_id', '!=', $userId)
                    ->where('is_read', false)
                    ->count();
    }

    /**
     * Mark all messages as read for a user.
     */
    public function markAsReadForUser($userId)
    {
        return $this->messages()
                    ->where('user_id', '!=', $userId)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
    }

    /**
     * Scope a query to find conversation between two users.
     */
    public function scopeBetweenUsers($query, $user1, $user2)
    {
        return $query->where(function($q) use ($user1, $user2) {
            $q->where('user_one', $user1)
              ->where('user_two', $user2);
        })->orWhere(function($q) use ($user1, $user2) {
            $q->where('user_one', $user2)
              ->where('user_two', $user1);
        });
    }
}