<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Theater;

class TicketPrice extends Model
{
    protected $fillable = ['theater_id', 'type', 'price'];

    public function theater() {
        return $this->belongsTo(Theater::class);
    }
}
