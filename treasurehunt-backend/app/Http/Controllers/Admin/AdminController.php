<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotspot;
use App\Models\Scene;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // --- Scenes ---

    public function indexScenes()
    {
        return response()->json([
            'success' => true,
            'data' => Scene::with('hotspots.targetScene')->get()
        ]);
    }

    public function showScene($id)
    {
        $scene = Scene::with('hotspots.targetScene')->find($id);

        if (!$scene) {
            return response()->json([
                'success' => false,
                'message' => 'Scene not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $scene
        ]);
    }

    public function storeScene(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.sq' => 'required|string',
            'image_path' => 'required|string',
            'level' => 'required|integer|min:1|max:5',
            'is_initial' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $scene = Scene::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Scene created successfully.',
            'data' => $scene
        ], 201);
    }

    public function updateScene(Request $request, $id)
    {
        $scene = Scene::find($id);

        if (!$scene) {
            return response()->json([
                'success' => false,
                'message' => 'Scene not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|array',
            'title.en' => 'sometimes|string',
            'title.sq' => 'sometimes|string',
            'image_path' => 'sometimes|string',
            'level' => 'sometimes|integer|min:1|max:5',
            'is_initial' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $scene->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Scene updated successfully.',
            'data' => $scene->fresh()->load('hotspots.targetScene')
        ]);
    }

    public function deleteScene($id)
    {
        $scene = Scene::find($id);

        if (!$scene) {
            return response()->json([
                'success' => false,
                'message' => 'Scene not found.'
            ], 404);
        }

        // Delete associated hotspots first
        $scene->hotspots()->delete();
        $scene->delete();

        return response()->json([
            'success' => true,
            'message' => 'Scene deleted successfully.'
        ]);
    }

    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('image');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path(), $filename);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully.',
            'data' => [
                'image_path' => '/' . $filename,
                'url' => url('/api/scene-image-by-path/' . $filename),
            ]
        ], 201);
    }

    // --- Hotspots ---

    public function indexHotspots()
    {
        return response()->json([
            'success' => true,
            'data' => Hotspot::with('scene', 'targetScene')->get()
        ]);
    }

    public function showHotspot($id)
    {
        $hotspot = Hotspot::with('scene', 'targetScene')->find($id);

        if (!$hotspot) {
            return response()->json([
                'success' => false,
                'message' => 'Hotspot not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $hotspot
        ]);
    }

    public function storeHotspot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scene_id' => 'required|exists:scenes,id',
            'type' => 'required|in:nav,treasure',
            'pitch' => 'required|numeric',
            'yaw' => 'required|numeric',
            'target_scene_id' => 'nullable|exists:scenes,id',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validation rules from schema:
        // if type=nav → target_scene_id required, data null
        // if type=treasure → target_scene_id null, data required with question/answers
        if ($request->type === 'nav') {
            if (!$request->target_scene_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Navigation hotspots require a target scene.'
                ], 422);
            }
        }

        if ($request->type === 'treasure') {
            if (!$request->data || !isset($request->data['question']) || !isset($request->data['answers'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Treasure hotspots require data with question and answers.'
                ], 422);
            }
        }

        $hotspot = Hotspot::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Hotspot created successfully.',
            'data' => $hotspot->load('scene', 'targetScene')
        ], 201);
    }

    public function updateHotspot(Request $request, $id)
    {
        $hotspot = Hotspot::find($id);

        if (!$hotspot) {
            return response()->json([
                'success' => false,
                'message' => 'Hotspot not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'scene_id' => 'sometimes|exists:scenes,id',
            'type' => 'sometimes|in:nav,treasure',
            'pitch' => 'sometimes|numeric',
            'yaw' => 'sometimes|numeric',
            'target_scene_id' => 'nullable|exists:scenes,id',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $hotspot->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Hotspot updated successfully.',
            'data' => $hotspot->fresh()->load('scene', 'targetScene')
        ]);
    }

    public function deleteHotspot($id)
    {
        $hotspot = Hotspot::find($id);

        if (!$hotspot) {
            return response()->json([
                'success' => false,
                'message' => 'Hotspot not found.'
            ], 404);
        }

        $hotspot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hotspot deleted successfully.'
        ]);
    }

}
