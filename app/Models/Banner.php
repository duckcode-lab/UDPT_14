<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banner extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'image', 'description', 'link'];

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : asset('storage/' . $this->image);
    }

    public function getRemoteImageUrlAttribute(): ?string
    {
        return $this->image && Str::startsWith($this->image, ['http://', 'https://'])
            ? $this->image
            : null;
    }
}
