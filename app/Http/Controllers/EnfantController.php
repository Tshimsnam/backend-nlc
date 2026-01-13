<?php

namespace App\Http\Controllers;

use App\Models\DetailSupplementaire;
use App\Models\Enfant;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnfantController extends Controller
{

    public function index(Request $request)
{
    try {
        // pagination par 10
        $perPage = $request->get('per_page', 10);

        $enfants = Enfant::with(['parents', 'detailsSupplementaires'])->paginate($perPage);

        return response()->json([
            'message' => 'Liste des enfants paginée récupérée avec succès.',
            'data' => $enfants
        ], 200);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Erreur lors de la récupération des enfants.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {
        $request->validate([
            'enfant.nom' => 'required|string',
            'enfant.genre' => 'required|in:Masculin,Féminin,Autre',
            'enfant.date_naissance' => 'required|date',
            'enfant.diagnostic_initial' => 'nullable|string',
            'enfant.notes_medicales' => 'nullable|string',

            'parents' => 'sometimes|array|min:1|max:2',
            'parents.*.nom' => 'required_with:parents|string',
            'parents.*.email' => 'required_with:parents|email',
            'parents.*.relation' => 'required_with:parents|string',
            'parents.*.telephone' => 'required_with:parents|string',

            'details.contact_urgence' => 'sometimes|string',
            'details.telephone_urgence' => 'sometimes|string',
            'details.medecin_traitant' => 'nullable|string',
            'details.telephone_medecin' => 'nullable|string',
            'details.allergies_conditions' => 'nullable|string',
            'details.notes_additionnelles' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Création de l'enfant
            $dataEnfant = $request->input('enfant');
            $enfant = Enfant::create($dataEnfant);

            // Création et attachement des parents siles info présents
            if ($request->has('parents')) {
                foreach ($request->input('parents') as $parentData) {
                    $parent = ParentModel::create($parentData);
                    $enfant->parents()->attach($parent->id);
                }
            }

            // Création des détails supplémentaires si les info sont disponible
            if ($request->has('details')) {
                $detail = new DetailSupplementaire($request->input('details'));
                $detail->enfant_id = $enfant->id;
                $detail->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Enfant enregistré avec succès.',
                'enfant' => $enfant->load('parents', 'detailsSupplementaires')
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erreur lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //information complete par enfant
    public function show($id)
{
    $enfant = Enfant::with(['parents', 'detailsSupplementaires'])->find($id);

    if (!$enfant) {
        return response()->json([
            'message' => 'Aucune information disponible.'
        ], 404);
    }

    return response()->json([
        'enfant' => $enfant
    ], 200);
}

public function destroy($id)
{
    try {
        $enfant = Enfant::with('detailsSupplementaires', 'parents')->findOrFail($id);
        $enfant->parents()->detach();
        $enfant->detailsSupplementaires()->delete();
        $enfant->delete();

        return response()->json([
            'message' => 'Enfant supprimé avec succès.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la suppression.',
            'error' => $e->getMessage()
        ], 500);
    }
}


}
