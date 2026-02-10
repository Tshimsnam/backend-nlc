<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_price_id' => ['required', 'integer', 'exists:event_prices,id'],
            'full_name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'min:9'],
            'days' => ['nullable', 'integer', 'min:1'],
            'pay_type' => ['required', 'string', 'max:50', 'in:mobile_money,credit_card,maxicash,paypal'],
            'pay_sub_type' => ['nullable', 'string', 'max:50'],
            'success_url' => ['nullable', 'string', 'url', 'max:500'],
            'cancel_url' => ['nullable', 'string', 'url', 'max:500'],
            'failure_url' => ['nullable', 'string', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_price_id.required' => 'Le tarif est obligatoire',
            'event_price_id.exists' => 'Le tarif sélectionné n\'existe pas',
            'full_name.required' => 'Le nom complet est obligatoire',
            'full_name.min' => 'Le nom doit contenir au moins 3 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'phone.required' => 'Le téléphone est obligatoire',
            'phone.min' => 'Le numéro de téléphone doit contenir au moins 9 chiffres',
            'pay_type.required' => 'Le mode de paiement est obligatoire',
            'pay_type.in' => 'Le mode de paiement sélectionné n\'est pas valide',
            'success_url.url' => 'L\'URL de succès doit être valide',
            'cancel_url.url' => 'L\'URL d\'annulation doit être valide',
            'failure_url.url' => 'L\'URL d\'échec doit être valide',
        ];
    }
}
