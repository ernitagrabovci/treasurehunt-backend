<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Scene;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if levels table is empty (safe to re-run)
        if (Level::count() > 0) {
            return;
        }

        $levels = Scene::select('level')
            ->distinct()
            ->orderBy('level')
            ->get();

        foreach ($levels as $s) {
            $first = Scene::where('level', $s->level)->orderBy('id')->first();

            Level::create([
                'title' => ['en' => "Level {$s->level}", 'sq' => "Niveli {$s->level}"],
                'image_path' => $first?->image_path,
                'scene_id' => $first?->id,
                'order' => $s->level,
            ]);
        }

        $this->command->info('Levels seeded from existing scenes.');
    }
}
