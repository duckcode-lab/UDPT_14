<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Showtime extends Model
{
    use HasFactory;
    protected $fillable = ['movie_id', 'theater_id', 'ticket_price_id', 'room', 'time'];
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function theater()
    {
        return $this->belongsTo(Theater::class);
    }

    public function ticketPrice()
    {
        return $this->belongsTo(TicketPrice::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'showtime_id');
    }

}
