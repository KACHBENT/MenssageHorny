<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Contracts\Auth\MustVerifyEmail;
=======
>>>>>>> main
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
<<<<<<< HEAD
=======
use App\Models\BiometricCredential;
use App\Models\BiometricAuthLog;
>>>>>>> main

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
<<<<<<< HEAD
    'name',
    'email',
    'password',
    'is_online',
    'last_seen',
=======
        'name',
        'email',
        'password',
        'is_online',
        'last_seen',
>>>>>>> main
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
    ];

    /**
     * Get the messages sent by the user.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the conversations of the user.
     */
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
<<<<<<< HEAD
                    ->withTimestamps();
=======
            ->withTimestamps();
>>>>>>> main
    }

    /**
     * Get all contacts (users the current user has chatted with).
     */
    public function contacts()
    {
        // Obtener conversaciones donde el usuario participa
        $conversations = Conversation::where('user_one', $this->id)
<<<<<<< HEAD
                        ->orWhere('user_two', $this->id)
                        ->get();
        
        $contacts = collect();
        
        foreach($conversations as $conversation) {
            $contactId = ($conversation->user_one == $this->id) 
                        ? $conversation->user_two 
                        : $conversation->user_one;
            
=======
            ->orWhere('user_two', $this->id)
            ->get();

        $contacts = collect();

        foreach ($conversations as $conversation) {
            $contactId = ($conversation->user_one == $this->id)
                ? $conversation->user_two
                : $conversation->user_one;

>>>>>>> main
            $contact = User::find($contactId);
            if ($contact) {
                $contacts->push($contact);
            }
        }
<<<<<<< HEAD
        
=======

>>>>>>> main
        return $contacts->unique('id');
    }

    /**
     * Check if user is online.
     */
    public function isOnline()
    {
        return $this->is_online;
    }

    /**
     * Get last seen attribute in human readable format.
     */
    public function getLastSeenAttribute($value)
    {
        if ($value) {
            return \Carbon\Carbon::parse($value)->diffForHumans();
        }
        return null;
    }
<<<<<<< HEAD
}
=======

    public function biometricCredentials()
    {
        return $this->hasMany(BiometricCredential::class);
    }

    public function biometricLogs()
    {
        return $this->hasMany(BiometricAuthLog::class);
    }
      public function faceBiometric()
    {
        return $this->hasOne(FaceBiometric::class);
    }

}
>>>>>>> main
