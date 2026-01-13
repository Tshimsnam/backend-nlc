<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Program::with(['child', 'creator', 'courses']);

        // Filtrer par enfant si spécifié
        if ($request->has('child_id')) {
            $query->where('child_id', $request->child_id);
        }

        // Filtrer par statut si spécifié
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer par créateur si spécifié
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $programs = $query->paginate(15);

        return response()->json($programs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'child_id' => 'required|exists:children,id',
            'created_by' => 'required|exists:users,id',
            'status' => 'nullable|in:pending,approved,rejected,active,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'objectives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $program = Program::create($request->all());

        return response()->json([
            'message' => 'Programme créé avec succès',
            'data' => $program->load(['child', 'creator'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $program = Program::with(['child', 'creator', 'courses'])->findOrFail($id);

        return response()->json($program);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $program = Program::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'child_id' => 'sometimes|exists:children,id',
            'status' => 'sometimes|in:pending,approved,rejected,active,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'objectives' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $program->update($request->all());

        return response()->json([
            'message' => 'Programme mis à jour avec succès',
            'data' => $program->load(['child', 'creator'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return response()->json([
            'message' => 'Programme supprimé avec succès'
        ]);
    }
}
