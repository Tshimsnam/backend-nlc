<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Setting::query();

        // Filtrer par catégorie si spécifié
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filtrer par visibilité publique si spécifié
        if ($request->has('is_public')) {
            $query->where('is_public', $request->is_public);
        }

        $settings = $query->paginate(15);

        return response()->json($settings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:100|unique:settings,key',
            'value' => 'nullable|array',
            'category' => 'required|in:general,security,notifications,backup,organization',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting = Setting::create($request->all());

        return response()->json([
            'message' => 'Paramètre créé avec succès',
            'data' => $setting
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $setting = Setting::findOrFail($id);

        return response()->json($setting);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $setting = Setting::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'key' => 'sometimes|string|max:100|unique:settings,key,' . $id,
            'value' => 'nullable|array',
            'category' => 'sometimes|in:general,security,notifications,backup,organization',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting->update($request->all());

        return response()->json([
            'message' => 'Paramètre mis à jour avec succès',
            'data' => $setting
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json([
            'message' => 'Paramètre supprimé avec succès'
        ]);
    }
}
