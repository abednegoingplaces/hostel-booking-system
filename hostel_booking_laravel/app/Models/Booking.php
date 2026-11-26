<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

    /** "status" is not fillable: it only changes through approve, reject and cancel. */
    protected $fillable = ['room_id', 'semester'];

    protected $attributes = [
        'status' => 'pending',
    ];

    /** A booking belongs to a student. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** A booking belongs to a room. */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
