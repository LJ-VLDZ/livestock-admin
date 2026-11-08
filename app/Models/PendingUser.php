<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'barangay',
        'birthday',
        'age',
        'token',
        'expires_at',
    ];
}