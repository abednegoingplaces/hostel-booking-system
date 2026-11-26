<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    use HasFactory;

    protected $fillable = ['hostel_id', 'room_number', 'capacity', 'price_per_semester', 'photo_path'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'price_per_semester' => 'decimal:2',
        ];
    }

    /** A room belongs to a hostel. */
    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class);
    }

    /** A room has many bookings. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Adds "approved_count" for one semester, so free beds can be shown
     * without one extra query per room.
     */
    public function scopeWithFreeBeds(Builder $query, string $semester): Builder
    {
        return $query->withCount([
            'bookings as approved_count' => fn (Builder $q) => $q->where('semester', $semester)->where('status', 'approved'),
        ]);
    }

    /** Free beds = capacity minus approved bookings for the semester (use with withFreeBeds). */
    protected function freeBeds(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->capacity - (int) ($this->approved_count ?? 0)));
    }

    /** Count approved bookings straight from the database (used when approving). */
    public function approvedCount(string $semester): int
    {
        return $this->bookings()->where('semester', $semester)->where('status', 'approved')->count();
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null);
    }
}
