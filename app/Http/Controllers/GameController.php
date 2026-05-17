<?php

namespace App\Http\Controllers;

use App\Models\Hotspot;
use App\Models\Scene;
use App\Models\User;
use App\Models\UserTreasure;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function scenes()
    {
        $scenes = Scene::with('hotspots')->orderBy('level')->get();

        return response()->json([
            'success' => true,
            'data' => $scenes
        ]);
    }

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

    public function scene($id)
    {
        $scene = Scene::with('hotspots')->findOrFail($id);

        return response()->json([
            'scene' => $scene,
            'hotspots' => $scene->hotspots
        ]);
    }

    public function navigate(Request $request)
    {
        $request->validate([
            'hotspot_id' => 'required|exists:hotspots,id'
        ]);

        $hotspot = Hotspot::with('targetScene.hotspots')->findOrFail($request->hotspot_id);

        if ($hotspot->type !== 'nav') {
            return response()->json([
                'success' => false,
                'message' => 'This hotspot is not a navigation hotspot.'
            ], 422);
        }

        if (!$hotspot->target_scene_id) {
            return response()->json([
                'success' => false,
                'message' => 'This navigation hotspot has no target scene.'
            ], 422);
        }

        $targetScene = $hotspot->targetScene;

        return response()->json([
            'success' => true,
            'scene' => $targetScene,
            'hotspots' => $targetScene->hotspots
        ]);
    }

    public function treasures()
    {
        $treasures = UserTreasure::with('hotspot.scene')
            ->where('user_id', request()->user()->id)
            ->orderBy('found_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $treasures->count(),
            'data' => $treasures
        ]);
    }

    public function checkTreasure($hotspotId)
    {
        $found = UserTreasure::where('user_id', request()->user()->id)
            ->where('hotspot_id', $hotspotId)
            ->exists();

        return response()->json([
            'found' => $found
        ]);
    }

    public function markTreasureFound(Request $request)
    {
        $request->validate([
            'hotspot_id' => 'required|exists:hotspots,id',
            'time_spent_seconds' => 'sometimes|integer|min:0',
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
            'found_at' => now(),
            'time_spent_seconds' => $request->time_spent_seconds,
        ]);

        return response()->json(['message' => 'treasure discovered']);
    }

    public function leaderboard(Request $request)
    {
        $level = $request->query('level');

        if ($level) {
            // Only show users who completed this level
            $leaderboard = UserTreasure::selectRaw('user_id, MIN(time_spent_seconds) as total_time')
                ->whereHas('hotspot.scene', fn($q) => $q->where('level', $level))
                ->whereNotNull('time_spent_seconds')
                ->with('user')
                ->get()
                ->sortBy('total_time')
                ->values()
                ->map(fn($ut, $i) => [
                    'rank' => $i + 1,
                    'name' => $ut->user->name,
                    'total_time' => (int) $ut->total_time,
                ]);

            return response()->json([
                'success' => true,
                'leaderboard' => $leaderboard,
                'level' => (int) $level,
            ]);
        }

        // Default: total treasures leaderboard
        $users = User::where('role', 'user')
            ->withCount('treasures')
            ->withSum('treasures', 'time_spent_seconds')
            ->orderBy('treasures_count', 'desc')
            ->get()
            ->map(fn($u, $i) => [
                'rank' => $i + 1,
                'name' => $u->name,
                'total_treasures' => $u->treasures_count,
                'total_time' => $u->treasures_sum_time_spent_seconds ?? 0,
                'points' => $u->treasures_count * 10,
                'level' => $u->level,
            ]);

        return response()->json([
            'success' => true,
            'leaderboard' => $users,
            'total_players' => User::where('role', 'user')->count(),
            'total_treasures_found' => UserTreasure::count(),
        ]);
    }

    public function myLevel()
    {
        $user = request()->user();
        $levelData = $user->level;

        $levelsWithScenes = Scene::select('level')
            ->distinct()
            ->pluck('level')
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $levelData + [
                'current_level' => $user->current_level,
                'levels_with_scenes' => $levelsWithScenes,
            ],
        ]);
    }

    public function levels()
    {
        try {
            $dbLevels = \App\Models\Level::orderBy('order')->get();
            if ($dbLevels->isNotEmpty()) {
                $data = $dbLevels->map(function ($level) {
                    return [
                        'id' => $level->id,
                        'title' => $level->title,
                        'image_url' => $level->image_path,
                        'scene_id' => $level->scene_id,
                        'order' => $level->order,
                    ];
                });
                return response()->json(['success' => true, 'data' => $data]);
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet — fall through
        }

        $distinctLevels = Scene::select('level')
            ->distinct()
            ->orderBy('level')
            ->pluck('level');

        $data = $distinctLevels->map(function ($level) {
            $first = Scene::where('level', $level)->orderBy('id')->first();
            return [
                'id' => $level,
                'title' => ['en' => "Level $level", 'sq' => "Niveli $level"],
                'image_url' => $first?->image_path,
                'scene_id' => $first?->id,
                'order' => $level,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function levelProgress()
    {
        $user = request()->user();
        $currentLevel = $user->current_level;

        $sceneIds = Scene::where('level', $currentLevel)->pluck('id');
        $totalTreasures = Hotspot::whereIn('scene_id', $sceneIds)->where('type', 'treasure')->count();
        $foundTreasures = UserTreasure::where('user_id', $user->id)
            ->whereIn('hotspot_id', Hotspot::whereIn('scene_id', $sceneIds)->where('type', 'treasure')->pluck('id'))
            ->count();

        $completed = $totalTreasures > 0 && $foundTreasures >= $totalTreasures;
        $nextLevelUnlocked = $currentLevel < 5 && $completed;

        return response()->json([
            'success' => true,
            'data' => [
                'current_level' => $currentLevel,
                'total_treasures' => $totalTreasures,
                'found_treasures' => $foundTreasures,
                'completed' => $completed,
                'next_level_unlocked' => $nextLevelUnlocked,
                'max_level' => 5,
            ],
        ]);
    }

    public function sceneImage($id)
    {
        $scene = Scene::findOrFail($id);
        $path = public_path(ltrim($scene->image_path, '/'));

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function advanceLevel()
    {
        $user = request()->user();

        if ($user->current_level >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Already at max level.',
            ], 422);
        }

        $user->increment('current_level');

        $nextScene = Scene::where('level', $user->current_level)->orderBy('id')->first();

        return response()->json([
            'success' => true,
            'message' => 'Advanced to level ' . $user->current_level,
            'current_level' => $user->current_level,
            'scene' => $nextScene ? $nextScene->load('hotspots') : null,
        ]);
    }
}
