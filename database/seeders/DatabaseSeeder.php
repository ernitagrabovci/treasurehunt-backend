<?php

namespace Database\Seeders;

use App\Models\Hotspot;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'locale' => 'en',
            'email_verified_at' => now(),
        ]);

        // Users
        foreach ([
            ['name' => 'Test User', 'email' => 'user@test.com', 'locale' => 'en'],
            ['name' => 'Alice', 'email' => 'alice@test.com', 'locale' => 'en'],
            ['name' => 'Bob', 'email' => 'bob@test.com', 'locale' => 'en'],
            ['name' => 'Era', 'email' => 'era@test.com', 'locale' => 'sq'],
        ] as $u) {
            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => bcrypt('password'),
                'role' => 'user',
                'locale' => $u['locale'],
                'email_verified_at' => now(),
            ]);
        }

        // --- 5 levels — each can have ANY number of rooms ---
        // Only ONE scene per level has a treasure.
        // Levels can have 1 room or 20 rooms — no limit.
        $levelData = [
            1 => [
                'name' => 'Level 1',
                'scenes' => [
                    [
                        'title' => ['en' => 'Living Room', 'sq' => 'Dhoma e ndenjes'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => true,
                        'treasure' => [
                            'pitch' => 5, 'yaw' => -15,
                            'question' => 'Which mountain range is visible in this room?',
                            'answers' => [
                                ['text' => 'The Alps', 'correct' => true],
                                ['text' => 'The Himalayas', 'correct' => false],
                                ['text' => 'The Andes', 'correct' => false],
                                ['text' => 'The Rockies', 'correct' => false],
                            ],
                        ],
                        'navs' => [['pitch' => -10, 'yaw' => 30, 'target' => [1, 1]]], // -> Hallway
                    ],
                    [
                        'title' => ['en' => 'Hallway', 'sq' => 'Korridori'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => 0, 'yaw' => -90, 'target' => [1, 0]]], // -> Living Room
                    ],
                ],
            ],
            2 => [
                'name' => 'Level 2',
                'scenes' => [
                    [
                        'title' => ['en' => 'Kitchen', 'sq' => 'Kuzhina'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => true,
                        'treasure' => [
                            'pitch' => 2, 'yaw' => -85,
                            'question' => 'What is the capital of Kosovo?',
                            'answers' => [
                                ['text' => 'Prishtina', 'correct' => true],
                                ['text' => 'Prizren', 'correct' => false],
                                ['text' => 'Mitrovica', 'correct' => false],
                                ['text' => 'Peja', 'correct' => false],
                            ],
                        ],
                        'navs' => [
                            ['pitch' => 0, 'yaw' => 0, 'target' => [2, 1]],    // -> Dining Room
                            ['pitch' => 10, 'yaw' => -45, 'target' => [2, 2]], // -> Pantry
                        ],
                    ],
                    [
                        'title' => ['en' => 'Dining Room', 'sq' => 'Dhoma e ngrënies'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => 0, 'yaw' => 180, 'target' => [2, 0]]], // -> Kitchen
                    ],
                    [
                        'title' => ['en' => 'Pantry', 'sq' => 'Qilarja'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => -5, 'yaw' => -90, 'target' => [2, 0]]], // -> Kitchen
                    ],
                ],
            ],
            3 => [
                'name' => 'Level 3',
                'scenes' => [
                    [
                        'title' => ['en' => 'Garden', 'sq' => 'Kopshti'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => true,
                        'treasure' => [
                            'pitch' => -5, 'yaw' => 90,
                            'question' => 'What is the largest lake in Kosovo?',
                            'answers' => [
                                ['text' => 'Gazivoda Lake', 'correct' => true],
                                ['text' => 'Badovc Lake', 'correct' => false],
                                ['text' => 'Batllava Lake', 'correct' => false],
                                ['text' => 'Radonjiq Lake', 'correct' => false],
                            ],
                        ],
                        'navs' => [
                            ['pitch' => 10, 'yaw' => 0, 'target' => [3, 1]],   // -> Terrace
                            ['pitch' => -5, 'yaw' => 45, 'target' => [3, 2]],  // -> Poolside
                            ['pitch' => -15, 'yaw' => -30, 'target' => [3, 3]], // -> Gazebo
                        ],
                    ],
                    [
                        'title' => ['en' => 'Terrace', 'sq' => 'Tarraca'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => -10, 'yaw' => 180, 'target' => [3, 0]]], // -> Garden
                    ],
                    [
                        'title' => ['en' => 'Poolside', 'sq' => 'Pranë pishinës'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => -5, 'yaw' => -135, 'target' => [3, 0]]], // -> Garden
                    ],
                    [
                        'title' => ['en' => 'Gazebo', 'sq' => 'Gazeboi'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => 10, 'yaw' => 135, 'target' => [3, 0]]], // -> Garden
                    ],
                ],
            ],
            4 => [
                'name' => 'Level 4',
                'scenes' => [
                    [
                        'title' => ['en' => 'Bedroom', 'sq' => 'Dhoma e gjumit'],
                        'image_path' => '/360img1.jpg',
                        'has_treasure' => true,
                        'treasure' => [
                            'pitch' => 0, 'yaw' => 45,
                            'question' => 'Which city is known as the "Pearl of Kosovo"?',
                            'answers' => [
                                ['text' => 'Prizren', 'correct' => true],
                                ['text' => 'Prishtina', 'correct' => false],
                                ['text' => 'Peja', 'correct' => false],
                                ['text' => 'Gjakova', 'correct' => false],
                            ],
                        ],
                        'navs' => [['pitch' => -5, 'yaw' => -45, 'target' => [4, 1]]], // -> Study
                    ],
                    [
                        'title' => ['en' => 'Study', 'sq' => 'Studioni'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => false,
                        'treasure' => null,
                        'navs' => [['pitch' => 0, 'yaw' => 135, 'target' => [4, 0]]], // -> Bedroom
                    ],
                ],
            ],
            5 => [
                'name' => 'Level 5',
                'scenes' => [
                    [
                        'title' => ['en' => 'Rooftop', 'sq' => 'Tarraca e lartë'],
                        'image_path' => '/360img.jpg',
                        'has_treasure' => true,
                        'treasure' => [
                            'pitch' => -10, 'yaw' => 0,
                            'question' => 'What is the newest country in Europe?',
                            'answers' => [
                                ['text' => 'Kosovo', 'correct' => true],
                                ['text' => 'Montenegro', 'correct' => false],
                                ['text' => 'Serbia', 'correct' => false],
                                ['text' => 'Croatia', 'correct' => false],
                            ],
                        ],
                        'navs' => [],
                    ],
                ],
            ],
        ];

        // Create all scenes, storing references by [level][scene_idx]
        $sceneRef = [];
        foreach ($levelData as $level => $data) {
            $sceneRef[$level] = [];
            foreach ($data['scenes'] as $idx => $sceneData) {
                $scene = Scene::create([
                    'title' => $sceneData['title'],
                    'image_path' => $sceneData['image_path'],
                    'is_initial' => ($level === 1 && $idx === 0),
                    'level' => $level,
                ]);
                $sceneRef[$level][$idx] = $scene;
            }
        }

        // Create nav hotspots (resolve target references)
        foreach ($levelData as $level => $data) {
            foreach ($data['scenes'] as $idx => $sceneData) {
                $scene = $sceneRef[$level][$idx];
                foreach ($sceneData['navs'] as $nav) {
                    $targetLevel = $nav['target'][0];
                    $targetIdx = $nav['target'][1];
                    $targetScene = $sceneRef[$targetLevel][$targetIdx];
                    Hotspot::create([
                        'scene_id' => $scene->id,
                        'type' => 'nav',
                        'pitch' => $nav['pitch'],
                        'yaw' => $nav['yaw'],
                        'target_scene_id' => $targetScene->id,
                    ]);
                }
            }
        }

        // Create treasure hotspots (only for scenes that have one)
        foreach ($levelData as $level => $data) {
            foreach ($data['scenes'] as $idx => $sceneData) {
                if ($sceneData['has_treasure'] && $sceneData['treasure']) {
                    $scene = $sceneRef[$level][$idx];
                    $t = $sceneData['treasure'];
                    Hotspot::create([
                        'scene_id' => $scene->id,
                        'type' => 'treasure',
                        'pitch' => $t['pitch'],
                        'yaw' => $t['yaw'],
                        'data' => [
                            'question' => $t['question'],
                            'answers' => $t['answers'],
                        ],
                    ]);
                }
            }
        }
    }
}
