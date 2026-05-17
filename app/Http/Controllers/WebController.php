<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\User;
use App\Models\UserTreasure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

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

    public function embedScene($id, Request $request)
    {
        // Optional token-based auth (used by web play pages)
        $token = $request->query('token');
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                Auth::login($accessToken->tokenable);
            }
        }

        $scene = Scene::with('hotspots.targetScene')->findOrFail($id);

        $scenesByLevel = Scene::select('id', 'level', 'title')
            ->orderBy('level')
            ->orderBy('id')
            ->get()
            ->groupBy('level');

        return view('embed.scene', compact('scene', 'scenesByLevel'));
    }
}
