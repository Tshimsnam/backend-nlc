<?php

namespace App\Http\Requests;

use App\Enums\DurationType;
use App\Enums\ParticipantCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'integer', 'exists:events,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'category' => ['required', Rule::enum(ParticipantCategory::class)],
            'duration_type' => ['required', Rule::enum(DurationType::class)],
        ];
    }
}
