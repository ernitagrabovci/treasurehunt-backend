<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreasureHunt extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'difficulty',
        'points',
        'image_url',
        'is_active'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}