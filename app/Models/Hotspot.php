<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotspot extends Model
{
    protected $fillable = ['scene_id', 'type', 'pitch', 'yaw', 'target_scene_id', 'data'];
    
    protected $casts = [
        'data' => 'array',
        'pitch' => 'float',
        'yaw' => 'float'
    ];
    
    public function scene()
    {
        return $this->belongsTo(Scene::class);
    }
}