<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Rules\CurrentPassword;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Registrar regla de validación personalizada
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            return Hash::check($value, Auth::user()->password);
        }, 'La contraseña actual no es correcta.');
    }
}