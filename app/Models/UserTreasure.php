<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTreasure extends Model
{
    protected $fillable = ['user_id', 'hotspot_id', 'found_at', 'time_spent_seconds'];

    protected $casts = [
        'found_at' => 'datetime',
        'time_spent_seconds' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hotspot()
    {
        return $this->belongsTo(Hotspot::class);
    }
}