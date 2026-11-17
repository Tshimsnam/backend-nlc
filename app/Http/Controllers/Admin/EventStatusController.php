<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventStatusController extends Controller
{
    public function update(Request $request, Event $event)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['upcoming', 'past'])]]);
        if ($data['status'] === 'upcoming' && $event->hasEnded()) {
            return back()->with('error', 'La date de fin est déjà dépassée. Modifiez les dates avant de rouvrir cet événement.');
        }
        $event->update($data);

        return redirect()->route('admin.dashboard.view', ['tab' => 'events'])
            ->with('success', $event->status === 'past' ? 'Événement marqué comme terminé. Les inscriptions sont closes.' : 'Événement marqué comme à venir.');
    }
}
