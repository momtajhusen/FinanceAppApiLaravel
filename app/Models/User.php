<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens; // Import the trait

class User extends Authenticatable
{
    use HasApiTokens, Notifiable; // Add the trait here

    protected $fillable = [
        'name',
        'email',
        'password',
        'currency',
        'phone',
        'profile_image_url',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
