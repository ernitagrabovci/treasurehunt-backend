<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'locale',
        'current_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'locale' => 'string',
    ];

    public function treasures()
    {
        return $this->hasMany(UserTreasure::class);
    }

    public function getLevelAttribute()
    {
        $count = $this->treasures()->count();

        $levels = [
            1 => ['level' => 1, 'title' => ['en' => 'Explorer', 'sq' => 'Eksplorues'], 'min' => 0],
            2 => ['level' => 2, 'title' => ['en' => 'Seeker', 'sq' => 'Kërkues'], 'min' => 2],
            3 => ['level' => 3, 'title' => ['en' => 'Hunter', 'sq' => 'Gjuetar'], 'min' => 4],
            4 => ['level' => 4, 'title' => ['en' => 'Collector', 'sq' => 'Koleksionues'], 'min' => 7],
            5 => ['level' => 5, 'title' => ['en' => 'Legend', 'sq' => 'Legjendë'], 'min' => 10],
        ];

        $current = $levels[1];
        $next = null;

        foreach ($levels as $lvl) {
            if ($count >= $lvl['min']) {
                $current = $lvl;
            }
        }

        if ($current['level'] < 5) {
            $next = $levels[$current['level'] + 1];
        }

        return [
            'level' => $current['level'],
            'title' => $current['title'],
            'total_treasures' => $count,
            'points' => $count * 10,
            'next_level_at' => $next ? $next['min'] : null,
            'treasures_to_next' => $next ? $next['min'] - $count : 0,
        ];
    }
}