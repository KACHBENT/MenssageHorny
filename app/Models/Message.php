<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'message',
        'type',
        'file_path',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['file_url'];

    /**
     * Encrypt the message when setting.
     */
    public function setMessageAttribute($value)
    {
        if ($value) {
            $this->attributes['message'] = Crypt::encryptString($value);
        } else {
            $this->attributes['message'] = null;
        }
    }

    /**
     * Decrypt the message when getting.
     */
    public function getMessageAttribute($value)
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return '[Mensaje encriptado]';
            }
        }
        return null;
    }

    /**
     * Get the file URL attribute.
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }

    /**
     * Get the user that sent the message.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversation of the message.
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Check if message is a text message.
     */
    public function isText()
    {
        return $this->type === 'text';
    }

    /**
     * Check if message is an image.
     */
    public function isImage()
    {
        return $this->type === 'image';
    }

    /**
     * Check if message is a video.
     */
    public function isVideo()
    {
        return $this->type === 'video';
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope a query to only include messages from a specific user.
     */
    public function scopeFromUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}