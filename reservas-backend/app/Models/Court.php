<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
    'name',
    'type',
    'price',
    'open_time',
    'close_time',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}