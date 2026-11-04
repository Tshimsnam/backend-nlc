<?php

namespace App\Services\Consortium;

use Illuminate\Validation\Rule;

class Fields
{
    public static function display(string $key, mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        if (is_array($value)) {
            $choices = $key === 'needs' ? config('consortium.needs') : ($key === 'expertise' ? config('consortium.expertise') : []);

            return implode(', ', array_map(fn ($item) => $choices[$item] ?? $item, $value));
        }
        $choices = match ($key) {
            'legal_form' => ['asbl' => 'ASBL', 'ong' => 'ONG', 'other' => 'Autre'],
            'structure_type' => ['association' => 'Association', 'centre' => 'Centre de prise en charge', 'school' => 'École spécialisée', 'clinic' => 'Établissement médical / clinique', 'other' => 'Autre'],
            default => [],
        };

        return (string) ($choices[$value] ?? $value);
    }

    public const LABELS = [
        'official_name' => 'Nom officiel', 'acronym' => 'Sigle', 'legal_form' => 'Forme juridique', 'legal_form_other' => 'Autre forme juridique',
        'structure_type' => 'Type de structure', 'authorization_number' => 'Numéro d’autorisation / arrêté', 'created_on' => 'Date de création',
        'address' => 'Adresse du siège', 'city' => 'Ville / commune', 'provinces' => 'Provinces d’intervention',
        'official_phone' => 'Téléphone officiel', 'official_email' => 'E-mail officiel', 'website' => 'Site web', 'facebook' => 'Page Facebook',
        'representative_name' => 'Nom et post-nom du représentant légal', 'representative_function' => 'Fonction du représentant',
        'representative_phone' => 'Téléphone du représentant', 'representative_email' => 'E-mail du représentant',
        'mission' => 'Présentation et mission', 'expertise' => 'Domaines d’intervention', 'expertise_other' => 'Autre expertise',
        'beneficiaries_children' => 'Enfants accompagnés', 'beneficiaries_adults' => 'Adultes accompagnés', 'staff_count' => 'Professionnels actifs',
        'services' => 'Services et capacités d’accueil', 'motivation' => 'Motivation pour rejoindre le consortium',
        'contributions' => 'Contributions au consortium', 'needs' => 'Besoins prioritaires', 'projects' => 'Projets envisagés',
        'signatory_name' => 'Nom du signataire', 'signatory_function' => 'Fonction du signataire',
        'accuracy_consent' => 'Exactitude des informations', 'data_consent' => 'Traitement du dossier', 'publicity_consent' => 'Publication du nom et du logo',
    ];

    public static function rules(bool $final = false): array
    {
        $presence = $final ? 'required' : 'nullable';
        $rules = [];
        foreach (array_keys(self::LABELS) as $field) {
            $rules[$field] = ['nullable', 'string', 'max:500'];
        }
        foreach (['official_name', 'authorization_number', 'address', 'city', 'representative_name', 'representative_function', 'signatory_name', 'signatory_function'] as $field) {
            $rules[$field] = [$presence, 'string', 'max:500'];
        }
        $rules['legal_form'] = [$presence, Rule::in(['asbl', 'ong', 'other'])];
        $rules['legal_form_other'] = [$final ? 'required_if:legal_form,other' : 'nullable', 'nullable', 'string', 'max:500'];
        $rules['structure_type'] = [$presence, Rule::in(['association', 'centre', 'school', 'clinic', 'other'])];
        $rules['created_on'] = [$presence, 'date_format:Y-m-d', 'before_or_equal:today'];
        $rules['provinces'] = [$presence, 'array', $final ? 'min:1' : 'min:0', 'max:26'];
        $rules['provinces.*'] = ['string', Rule::in(config('consortium.provinces')), 'distinct'];
        foreach (['official_phone', 'representative_phone'] as $field) {
            $rules[$field] = [$presence, 'string', 'regex:/^\+?[0-9\s().-]{8,25}$/'];
        }
        foreach (['official_email', 'representative_email'] as $field) {
            $rules[$field] = [$presence, 'email', 'max:255'];
        }
        foreach (['website', 'facebook'] as $field) {
            $rules[$field] = ['nullable', 'url:http,https', 'max:500'];
        }
        $rules['expertise'] = [$presence, 'array', $final ? 'min:1' : 'min:0', 'max:7'];
        $rules['expertise.*'] = [Rule::in(array_keys(config('consortium.expertise'))), 'distinct'];
        $rules['expertise_other'] = ['nullable', 'string', 'max:500'];
        $rules['needs'] = ['nullable', 'array', 'max:6'];
        $rules['needs.*'] = [Rule::in(array_keys(config('consortium.needs'))), 'distinct'];
        foreach (['beneficiaries_children', 'beneficiaries_adults', 'staff_count'] as $field) {
            $rules[$field] = [$presence, 'integer', 'min:0', 'max:1000000'];
        }
        foreach (['mission', 'motivation'] as $field) {
            $rules[$field] = [$presence, 'string', $final ? 'min:20' : 'min:0', 'max:5000'];
        }
        foreach (['services', 'contributions', 'projects'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:5000'];
        }
        foreach (['accuracy_consent', 'data_consent'] as $field) {
            $rules[$field] = $final ? ['accepted'] : ['nullable', 'boolean'];
        }
        $rules['publicity_consent'] = ['nullable', 'boolean'];

        return $rules;
    }
}
