<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    protected $fillable = ['title', 'image_path', 'scene_id', 'order'];

    protected $casts = [
        'title' => 'array',
        'order' => 'integer',
    ];

    public function scene()
    {
        return $this->belongsTo(Scene::class);
    }
}
