<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scene;
use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function dashboard()
    {
        $scenes = Scene::with('hotspots.targetScene')->orderBy('level')->orderBy('id')->get();
        $levels = range(1, 5);
        return view('admin.dashboard', compact('scenes', 'levels'));
    }
}
