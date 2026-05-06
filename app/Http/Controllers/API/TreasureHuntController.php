<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TreasureHunt;
use Illuminate\Http\Request;

class TreasureHuntController extends Controller
{
    public function index()
    {
        $hunts = TreasureHunt::where('is_active', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $hunts
        ]);
    }
    
    public function show($id)
    {
        $hunt = TreasureHunt::with('locations')->find($id);
        
        if (!$hunt) {
            return response()->json([
                'success' => false,
                'message' => 'Treasure hunt not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $hunt
        ]);
    }
}