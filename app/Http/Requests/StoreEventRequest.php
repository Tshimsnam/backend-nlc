<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->hasAny(['date', 'end_date']) || $validator->errors()->has('date') || $validator->errors()->has('end_date')) {
                return;
            }
            $event = $this->route('event');
            $start = $this->input('date', $event instanceof \App\Models\Event ? $event->date : null);
            $end = $this->input('end_date', $event instanceof \App\Models\Event ? $event->end_date : null);
            if (is_string($start) && is_string($end) && $end !== '' && $end < $start) {
                $validator->errors()->add('end_date', 'La date de fin doit être égale ou postérieure à la date de début.');
            }
        });
    }

    public function rules(): array
    {
        $updating = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $required = $updating ? 'sometimes' : 'required';
        $event = $this->route('event');
        $startDate = $this->input('date', $event instanceof \App\Models\Event ? $event->date : null);
        $slugRule = Rule::unique('events', 'slug');
        if ($updating && $this->route('event') instanceof \App\Models\Event) {
            $slugRule->ignore($this->route('event'));
        }

        return [
            'title' => [$required, 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'date' => [$required, 'date_format:Y-m-d'],
            'end_date' => array_filter(['nullable', 'date_format:Y-m-d', is_string($startDate) && $startDate !== '' ? 'after_or_equal:'.$startDate : null]),
            'time' => [$required, 'string'],
            'end_time' => ['nullable', 'regex:/^(?:[01]?\d|2[0-3])(?:h|:)[0-5]\d(?::[0-5]\d)?$/i'],
            'location' => [$required, 'string', 'max:255'],
            'venue_details' => ['nullable', 'string', 'max:255'],
            'type' => [$required, Rule::in(['workshop', 'celebration', 'seminar', 'gala', 'conference'])],
            'status' => ['sometimes', 'required', Rule::in(['upcoming', 'past'])],
            'image' => ['nullable', 'string', 'max:500'],
            'agenda' => ['nullable', 'array'],
            'agenda.*.day' => ['required_with:agenda', 'string'],
            'agenda.*.time' => ['required_with:agenda', 'string'],
            'agenda.*.activities' => ['required_with:agenda', 'string'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'registration_deadline' => ['nullable', 'date'],
            'sponsors' => ['nullable', 'array'],
            'sponsors.*' => ['string', 'max:255'],
        ];
    }
}
