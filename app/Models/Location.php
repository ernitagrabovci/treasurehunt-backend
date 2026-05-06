<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'treasure_hunt_id',
        'name',
        'latitude',
        'longitude',
        'address',
        'order_number'
    ];

    public function treasureHunt()
    {
        return $this->belongsTo(TreasureHunt::class);
    }

    public function clues()
    {
        return $this->hasMany(Clue::class);
    }
}