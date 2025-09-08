<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory; // ✅ add this
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable; // ✅ include HasFactory

    protected $fillable = ['name','email','password','role'];

    protected $hidden = ['password'];

    /**
     * JWTIdentifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWTCustomClaims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
