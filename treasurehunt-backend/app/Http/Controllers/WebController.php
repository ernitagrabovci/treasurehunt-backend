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

    public function embedScene($id)
    {
        $scene = Scene::with('hotspots.targetScene')->findOrFail($id);
        $scenesByLevel = Scene::select('id', 'level', 'title')
            ->orderBy('level')
            ->orderBy('id')
            ->get()
            ->groupBy('level');
        return view('embed.scene', compact('scene', 'scenesByLevel'));
    }
}
