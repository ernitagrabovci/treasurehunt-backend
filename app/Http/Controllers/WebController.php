<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\User;
use App\Models\UserTreasure;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function login()
    {
        return view('login');
    }

    public function play()
    {
        $scenes = Scene::with('hotspots')->orderBy('level')->get();
        return view('play', compact('scenes'));
    }

    public function leaderboard()
    {
        return view('leaderboard');
    }

    public function profile()
    {
        return view('profile');
    }
}
