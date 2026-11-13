<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seat extends Model
{
    use HasFactory;
    protected $fillable = ['showtime_id', 'seat_number', 'is_booked'];

    public function showtime() { return $this->belongsTo(Showtime::class); }
    public function ticket() { return $this->hasOne(Ticket::class); }
}
