<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\UserTreasure;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function initialScene()
    {
        $scene = Scene::with('hotspots')
            ->where('is_initial', true)
            ->firstOrFail();
            
        return response()->json([
            'scene' => $scene,
            'hotspots' => $scene->hotspots
        ]);
    }
    
    public function markTreasureFound(Request $request)
    {
        $request->validate([
            'hotspot_id' => 'required|exists:hotspots,id'
        ]);
        
        $alreadyFound = UserTreasure::where('user_id', $request->user()->id)
            ->where('hotspot_id', $request->hotspot_id)
            ->exists();
            
        if ($alreadyFound) {
            return response()->json(['message' => 'already found'], 422);
        }
        
        UserTreasure::create([
            'user_id' => $request->user()->id,
            'hotspot_id' => $request->hotspot_id,
            'found_at' => now()
        ]);
        
        return response()->json(['message' => 'treasure discovered']);
    }
}