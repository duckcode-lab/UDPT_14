<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Showtime extends Model
{
    use HasFactory;

    protected $fillable = ['movie_id', 'theater', 'room', 'time', 'price'];

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
