<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaceBiometric extends Model
{
    use HasFactory;

    protected $table = 'face_biometrics';

    protected $fillable = [
        'user_id',
        'face_descriptor',
        'is_enabled',
        'registered_at',
        'last_verified_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'registered_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}