<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (Str::startsWith($this->photo, ['http://', 'https://'])) {
            return $this->photo;
        }

        return Str::startsWith($this->photo, 'uploads/')
            ? asset('storage/' . $this->photo)
            : asset($this->photo);
    }

    public function getRemotePhotoUrlAttribute(): ?string
    {
        return $this->photo && Str::startsWith($this->photo, ['http://', 'https://'])
            ? $this->photo
            : null;
    }

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
