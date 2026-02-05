<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventPriceController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $prices = $event->eventPrices;

        return response()->json($prices);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', Rule::in(['medecin', 'etudiant', 'parent', 'enseignant'])],
            'duration_type' => ['required', Rule::in(['per_day', 'full_event'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['event_id'] = $event->id;
        $validated['currency'] = $validated['currency'] ?? 'USD';

        $eventPrice = EventPrice::create($validated);

        return response()->json([
            'message' => 'Tarif ajouté',
            'data' => $eventPrice,
        ], 201);
    }

    public function update(Request $request, Event $event, EventPrice $eventPrice): JsonResponse
    {
        $this->ensurePriceBelongsToEvent($event, $eventPrice);

        $validated = $request->validate([
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $eventPrice->update($validated);

        return response()->json([
            'message' => 'Tarif mis à jour',
            'data' => $eventPrice->fresh(),
        ]);
    }

    public function destroy(Event $event, EventPrice $eventPrice): JsonResponse
    {
        $this->ensurePriceBelongsToEvent($event, $eventPrice);
        $eventPrice->delete();

        return response()->json(['message' => 'Tarif supprimé'], 200);
    }

    private function ensurePriceBelongsToEvent(Event $event, EventPrice $eventPrice): void
    {
        if ((int) $eventPrice->event_id !== (int) $event->id) {
            abort(404);
        }
    }
}
