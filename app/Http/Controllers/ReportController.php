<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Report::with(['child', 'author']);

        // Filtrer par enfant si spécifié
        if ($request->has('child_id')) {
            $query->where('child_id', $request->child_id);
        }

        // Filtrer par auteur si spécifié
        if ($request->has('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Filtrer par type si spécifié
        if ($request->has('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        // Filtrer par confidentialité si spécifié
        if ($request->has('is_confidential')) {
            $query->where('is_confidential', $request->is_confidential);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($reports);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|exists:children,id',
            'author_id' => 'required|exists:users,id',
            'report_type' => 'required|in:progress,incident,evaluation,medical,behavioral,academic',
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'observations' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'is_confidential' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $report = Report::create($request->all());

        return response()->json([
            'message' => 'Rapport créé avec succès',
            'data' => $report->load(['child', 'author'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $report = Report::with(['child', 'author'])->findOrFail($id);

        return response()->json($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $report = Report::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'report_type' => 'sometimes|in:progress,incident,evaluation,medical,behavioral,academic',
            'title' => 'sometimes|string|max:200',
            'content' => 'sometimes|string',
            'observations' => 'nullable|array',
            'recommendations' => 'nullable|string',
            'is_confidential' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $report->update($request->all());

        return response()->json([
            'message' => 'Rapport mis à jour avec succès',
            'data' => $report->load(['child', 'author'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return response()->json([
            'message' => 'Rapport supprimé avec succès'
        ]);
    }
}
