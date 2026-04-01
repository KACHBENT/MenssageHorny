<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiometricAuthLog extends Model
{
    use HasFactory;

    protected $table = 'biometric_auth_logs';

    protected $fillable = [
        'user_id',
        'biometric_credential_id',
        'event',
        'device_id',
        'ip_address',
        'user_agent',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credential()
    {
        return $this->belongsTo(BiometricCredential::class, 'biometric_credential_id');
    }
}