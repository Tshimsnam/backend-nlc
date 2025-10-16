<?php

namespace App\Http\Controllers;

use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Child::with(['parent', 'dossier']);

        // Filtrer par parent si spécifié
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // Filtrer par statut si spécifié
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $children = $query->paginate(15);

        return response()->json($children);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'parent_id' => 'nullable|exists:users,id',
            'medical_info' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $child = Child::create($request->all());

        return response()->json([
            'message' => 'Enfant créé avec succès',
            'data' => $child->load('parent')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $child = Child::with(['parent', 'programs', 'appointments', 'reports', 'dossier'])->findOrFail($id);

        return response()->json($child);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $child = Child::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'date_of_birth' => 'sometimes|date',
            'parent_id' => 'nullable|exists:users,id',
            'medical_info' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,graduated,transferred',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $child->update($request->all());

        return response()->json([
            'message' => 'Enfant mis à jour avec succès',
            'data' => $child->load('parent')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $child = Child::findOrFail($id);
        $child->delete();

        return response()->json([
            'message' => 'Enfant supprimé avec succès'
        ]);
    }
}
