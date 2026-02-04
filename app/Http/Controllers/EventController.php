<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Liste des événements (lecture publique ou authentifiée).
     */
    public function index(Request $request)
    {
        $query = Event::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->orderBy('date')->paginate(15);

        return response()->json($events);
    }

    /**
     * Afficher un événement.
     */
    public function show(string $id)
    {
        $event = Event::findOrFail($id);
        return response()->json($event);
    }

    /**
     * Créer un événement (admin uniquement).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'date' => 'required|string',
            'end_date' => 'nullable|string',
            'time' => 'required|string',
            'end_time' => 'nullable|string',
            'location' => 'required|string|max:255',
            'type' => 'required|in:workshop,celebration,seminar,gala,conference',
            'status' => 'nullable|in:upcoming,past',
            'image' => 'nullable|string|max:500',
            'agenda' => 'nullable|array',
            'agenda.*.day' => 'required_with:agenda|string',
            'agenda.*.time' => 'required_with:agenda|string',
            'agenda.*.activities' => 'required_with:agenda|string',
            'price' => 'nullable|array',
            'price.standard' => 'nullable|numeric|min:0',
            'price.early' => 'nullable|numeric|min:0',
            'price.description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'registered' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        if (!isset($data['status'])) {
            $data['status'] = 'upcoming';
        }

        $event = Event::create($data);

        return response()->json([
            'message' => 'Événement créé avec succès',
            'data' => $event,
        ], 201);
    }

    /**
     * Modifier un événement (admin uniquement).
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:events,slug,' . $event->id,
            'description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'date' => 'sometimes|string',
            'end_date' => 'nullable|string',
            'time' => 'sometimes|string',
            'end_time' => 'nullable|string',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:workshop,celebration,seminar,gala,conference',
            'status' => 'nullable|in:upcoming,past',
            'image' => 'nullable|string|max:500',
            'agenda' => 'nullable|array',
            'agenda.*.day' => 'required_with:agenda|string',
            'agenda.*.time' => 'required_with:agenda|string',
            'agenda.*.activities' => 'required_with:agenda|string',
            'price' => 'nullable|array',
            'price.standard' => 'nullable|numeric|min:0',
            'price.early' => 'nullable|numeric|min:0',
            'price.description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'registered' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return response()->json([
            'message' => 'Événement mis à jour avec succès',
            'data' => $event->fresh(),
        ]);
    }

    /**
     * Supprimer un événement (admin uniquement).
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json([
            'message' => 'Événement supprimé avec succès',
        ], 200);
    }
}
