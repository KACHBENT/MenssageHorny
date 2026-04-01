<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BiometricCredential extends Model
{
    use HasFactory;

    protected $table = 'biometric_credentials';

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'key_alias',
        'public_key_pem',
        'enabled',
        'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(BiometricAuthLog::class, 'biometric_credential_id');
    }
}