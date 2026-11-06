<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','showtime_id','seat_id','status','price','booked_at'];

    public function user() { return $this->belongsTo(\App\Models\User::class); }
    public function showtime() { return $this->belongsTo(Showtime::class); }
    public function seat() { return $this->belongsTo(Seat::class); }
}

