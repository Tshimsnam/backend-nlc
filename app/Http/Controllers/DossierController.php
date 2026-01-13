<?php

namespace App\Http\Controllers;

use App\Models\Dossier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Dossier::with(['child']);

        // Filtrer par enfant si spécifié
        if ($request->has('child_id')) {
            $query->where('child_id', $request->child_id);
        }

        $dossiers = $query->paginate(15);

        return response()->json($dossiers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|exists:children,id|unique:dossiers,child_id',
            'medical_history' => 'nullable|array',
            'allergies' => 'nullable|array',
            'medications' => 'nullable|array',
            'emergency_contacts' => 'nullable|array',
            'educational_goals' => 'nullable|array',
            'behavioral_notes' => 'nullable|string',
            'documents' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dossier = Dossier::create($request->all());

        return response()->json([
            'message' => 'Dossier créé avec succès',
            'data' => $dossier->load('child')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dossier = Dossier::with(['child'])->findOrFail($id);

        return response()->json($dossier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $dossier = Dossier::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'medical_history' => 'nullable|array',
            'allergies' => 'nullable|array',
            'medications' => 'nullable|array',
            'emergency_contacts' => 'nullable|array',
            'educational_goals' => 'nullable|array',
            'behavioral_notes' => 'nullable|string',
            'documents' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $dossier->update($request->all());

        return response()->json([
            'message' => 'Dossier mis à jour avec succès',
            'data' => $dossier->load('child')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $dossier = Dossier::findOrFail($id);
        $dossier->delete();

        return response()->json([
            'message' => 'Dossier supprimé avec succès'
        ]);
    }
}
