<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'gender', 'description'];

    /** A hostel has many rooms. */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /** All bookings for any room in this hostel. */
    public function bookings()
    {
        return $this->hasManyThrough(Booking::class, Room::class);
    }
}
