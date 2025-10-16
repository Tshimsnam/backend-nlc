<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'recipient']);

        // Filtrer par expéditeur si spécifié
        if ($request->has('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        // Filtrer par destinataire si spécifié
        if ($request->has('recipient_id')) {
            $query->where('recipient_id', $request->recipient_id);
        }

        // Filtrer par statut de lecture si spécifié
        if ($request->has('is_read')) {
            $query->where('is_read', $request->is_read);
        }

        // Filtrer par priorité si spécifié
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:200',
            'content' => 'required|string',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = Message::create($request->all());

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'data' => $message->load(['sender', 'recipient'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $message = Message::with(['sender', 'recipient'])->findOrFail($id);

        // Marquer comme lu si c'est le destinataire qui le consulte
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        return response()->json($message);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $message = Message::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_read' => 'sometimes|boolean',
            'priority' => 'sometimes|in:low,normal,high,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('is_read') && $request->is_read && !$message->read_at) {
            $request->merge(['read_at' => now()]);
        }

        $message->update($request->all());

        return response()->json([
            'message' => 'Message mis à jour avec succès',
            'data' => $message
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = Message::findOrFail($id);
        $message->delete();

        return response()->json([
            'message' => 'Message supprimé avec succès'
        ]);
    }
}
