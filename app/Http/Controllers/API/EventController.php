<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with('event_prices');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $events = $query->orderBy('date')->get();

        return response()->json($events);
    }

    public function show(Event $event): JsonResponse
    {
        $event->load('event_prices');

        return response()->json($event);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['status'] = $data['status'] ?? 'upcoming';

        $event = Event::create($data);

        return response()->json([
            'message' => 'Événement créé avec succès',
            'data' => $event->load('event_prices'),
        ], 201);
    }

    public function update(StoreEventRequest $request, Event $event): JsonResponse
    {
        $event->update($request->validated());

        return response()->json([
            'message' => 'Événement mis à jour avec succès',
            'data' => $event->fresh()->load('event_prices'),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json(['message' => 'Événement supprimé avec succès'], 200);
    }
}
