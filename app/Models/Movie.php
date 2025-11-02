<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'duration',
        'photo',
        'trailer',
        'release_date',
        'status',
    ];
    public function showtimes()
    {
        return $this->hasMany(Showtime::class);
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
}
?>
