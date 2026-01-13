<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Notification::with(['user']);

        // Filtrer par utilisateur si spécifié
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtrer par type si spécifié
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filtrer par statut de lecture si spécifié
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($notifications);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|in:appointment,message,report,system,reminder,alert',
            'action_url' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $notification = Notification::create($request->all());

        return response()->json([
            'message' => 'Notification créée avec succès',
            'data' => $notification
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notification = Notification::with(['user'])->findOrFail($id);

        // Marquer comme lu automatiquement
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json($notification);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $notification = Notification::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_read' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('is_read') && $request->is_read && !$notification->read_at) {
            $request->merge(['read_at' => now()]);
        }

        $notification->update($request->all());

        return response()->json([
            'message' => 'Notification mise à jour avec succès',
            'data' => $notification
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'message' => 'Notification supprimée avec succès'
        ]);
    }
}
