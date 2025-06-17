<?php

namespace App\Http\Controllers;

use App\Models\DetailSupplementaire;
use App\Models\Enfant;
use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnfantController extends Controller
{
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
}
