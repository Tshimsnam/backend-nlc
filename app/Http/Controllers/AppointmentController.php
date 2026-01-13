<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['child', 'professional']);

        // Filtrer par enfant si spécifié
        if ($request->has('child_id')) {
            $query->where('child_id', $request->child_id);
        }

        // Filtrer par professionnel si spécifié
        if ($request->has('professional_id')) {
            $query->where('professional_id', $request->professional_id);
        }

        // Filtrer par type si spécifié
        if ($request->has('appointment_type')) {
            $query->where('appointment_type', $request->appointment_type);
        }

        // Filtrer par statut si spécifié
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('scheduled_at', 'asc')->paginate(15);

        return response()->json($appointments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'child_id' => 'required|exists:children,id',
            'professional_id' => 'required|exists:users,id',
            'appointment_type' => 'required|in:consultation,therapy,evaluation,follow_up,parent_meeting',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'nullable|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'location' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::create($request->all());

        return response()->json([
            'message' => 'Rendez-vous créé avec succès',
            'data' => $appointment->load(['child', 'professional'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $appointment = Appointment::with(['child', 'professional'])->findOrFail($id);

        return response()->json($appointment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'child_id' => 'sometimes|exists:children,id',
            'professional_id' => 'sometimes|exists:users,id',
            'appointment_type' => 'sometimes|in:consultation,therapy,evaluation,follow_up,parent_meeting',
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:scheduled,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
            'location' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $appointment->update($request->all());

        return response()->json([
            'message' => 'Rendez-vous mis à jour avec succès',
            'data' => $appointment->load(['child', 'professional'])
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'message' => 'Rendez-vous supprimé avec succès'
        ]);
    }
}
