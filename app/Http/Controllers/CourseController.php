<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Course::with(['program.child', 'educator']);

        // Filtrer par programme si spécifié
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filtrer par éducateur si spécifié
        if ($request->has('educator_id')) {
            $query->where('educator_id', $request->educator_id);
        }

        // Filtrer par statut si spécifié
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->paginate(15);

        return response()->json($courses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'program_id' => 'required|exists:programs,id',
            'educator_id' => 'nullable|exists:users,id',
            'duration_minutes' => 'nullable|integer|min:1',
            'materials' => 'nullable|array',
            'objectives' => 'nullable|array',
            'status' => 'nullable|in:scheduled,in_progress,completed,cancelled,rescheduled',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create($request->all());

        return response()->json([
            'message' => 'Cours créé avec succès',
            'data' => $course->load(['program', 'educator'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::with(['program.child', 'educator'])->findOrFail($id);

        return response()->json($course);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $course = Course::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'program_id' => 'sometimes|exists:programs,id',
            'educator_id' => 'nullable|exists:users,id',
            'duration_minutes' => 'nullable|integer|min:1',
            'materials' => 'nullable|array',
            'objectives' => 'nullable|array',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,rescheduled',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($request->all());

        return response()->json([
            'message' => 'Cours mis à jour avec succès',
            'data' => $course->load(['program', 'educator'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'message' => 'Cours supprimé avec succès'
        ]);
    }
}
