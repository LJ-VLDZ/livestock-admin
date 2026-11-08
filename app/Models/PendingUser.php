<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password' => 'encrypted',
        'barangay',
        'birthday' => 'date',
        'age',
        'token',
        'expires_at' => 'datetime',
    ];
}