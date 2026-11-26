<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mass-assignable fields. "role" is deliberately NOT here, so nobody can
     * make themselves a warden by adding role=warden to the registration form.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /** New accounts are students unless a seeder says otherwise. */
    protected $attributes = [
        'role' => 'student',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** A student has many bookings. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isWarden(): bool
    {
        return $this->role === 'warden';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }
}
