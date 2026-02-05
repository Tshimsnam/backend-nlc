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

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')],
            'description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'date' => ['required', 'string'],
            'end_date' => ['nullable', 'string'],
            'time' => ['required', 'string'],
            'end_time' => ['nullable', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['workshop', 'celebration', 'seminar', 'gala', 'conference'])],
            'status' => ['nullable', Rule::in(['upcoming', 'past'])],
            'image' => ['nullable', 'string', 'max:500'],
            'agenda' => ['nullable', 'array'],
            'agenda.*.day' => ['required_with:agenda', 'string'],
            'agenda.*.time' => ['required_with:agenda', 'string'],
            'agenda.*.activities' => ['required_with:agenda', 'string'],
            'price' => ['nullable', 'array'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'registered' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
