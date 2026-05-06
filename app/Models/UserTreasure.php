<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTreasure extends Model
{
    protected $fillable = ['user_id', 'hotspot_id', 'found_at'];
    
    protected $casts = [
        'found_at' => 'datetime'
    ];
}