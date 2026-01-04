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
        $query = Child::with(['parents', 'dossier']);

        // Filtrer par parent si spécifié
        if ($request->has('parent_id')) {
            $query->whereHas('parents', function($q) use ($request) {
                $q->where('users.id', $request->parent_id);
            });
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
            'medical_info' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            // Champs additionnels pour le formulaire complet
            'gender' => 'nullable|in:male,female,other',
            'diagnosis' => 'nullable|string|max:255',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
            'transport_info' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            // Informations médecin traitant
            'doctor_name' => 'nullable|string|max:255',
            'doctor_specialty' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_address' => 'nullable|string',
            // Contact d'urgence
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_phone2' => 'nullable|string|max:20',
            // Parents - tableau de parents
            'parents' => 'required|array|min:1',
            'parents.*.user_id' => 'required|exists:users,id',
            'parents.*.relationship' => 'required|in:mother,father,guardian,grandparent,other',
            'parents.*.is_primary' => 'boolean',
            'parents.*.has_custody' => 'boolean',
            'parents.*.emergency_contact_order' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Créer l'enfant
        $childData = $request->except('parents');
        $child = Child::create($childData);

        // Attacher les parents
        $parentsData = [];
        foreach ($request->parents as $parent) {
            $parentsData[$parent['user_id']] = [
                'relationship' => $parent['relationship'],
                'is_primary' => $parent['is_primary'] ?? false,
                'has_custody' => $parent['has_custody'] ?? true,
                'emergency_contact_order' => $parent['emergency_contact_order'] ?? null,
            ];
        }
        $child->parents()->attach($parentsData);

        return response()->json([
            'message' => 'Enfant créé avec succès',
            'data' => $child->load('parents')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $child = Child::with(['parents', 'programs', 'appointments', 'reports', 'dossier'])->findOrFail($id);

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
            'medical_info' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,graduated,transferred',
            // Champs additionnels pour le formulaire complet
            'gender' => 'nullable|in:male,female,other',
            'diagnosis' => 'nullable|string|max:255',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
            'transport_info' => 'nullable|string',
            'additional_notes' => 'nullable|string',
            // Informations médecin traitant
            'doctor_name' => 'nullable|string|max:255',
            'doctor_specialty' => 'nullable|string|max:255',
            'doctor_phone' => 'nullable|string|max:20',
            'doctor_address' => 'nullable|string',
            // Contact d'urgence
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_phone2' => 'nullable|string|max:20',
            // Parents - tableau de parents
            'parents' => 'sometimes|array|min:1',
            'parents.*.user_id' => 'required|exists:users,id',
            'parents.*.relationship' => 'required|in:mother,father,guardian,grandparent,other',
            'parents.*.is_primary' => 'boolean',
            'parents.*.has_custody' => 'boolean',
            'parents.*.emergency_contact_order' => 'nullable|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Mettre à jour les données de l'enfant
        $childData = $request->except('parents');
        $child->update($childData);

        // Mettre à jour les parents si fournis
        if ($request->has('parents')) {
            $parentsData = [];
            foreach ($request->parents as $parent) {
                $parentsData[$parent['user_id']] = [
                    'relationship' => $parent['relationship'],
                    'is_primary' => $parent['is_primary'] ?? false,
                    'has_custody' => $parent['has_custody'] ?? true,
                    'emergency_contact_order' => $parent['emergency_contact_order'] ?? null,
                ];
            }
            $child->parents()->sync($parentsData);
        }

        return response()->json([
            'message' => 'Enfant mis à jour avec succès',
            'data' => $child->load('parents')
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
