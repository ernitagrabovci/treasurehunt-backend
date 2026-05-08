<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $fillable = ['title', 'image_path', 'is_initial', 'level'];

    protected $casts = [
        'title' => 'array',
        'is_initial' => 'boolean',
        'level' => 'integer',
    ];

    public function hotspots()
    {
        return $this->hasMany(Hotspot::class);
    }
}
