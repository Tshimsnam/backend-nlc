<?php

namespace App\Enums;

enum ParticipantCategory: string
{
    case Medecin = 'medecin';
    case Etudiant = 'etudiant';
    case Parent = 'parent';
    case Enseignant = 'enseignant';

    public function label(): string
    {
        return match ($this) {
            self::Medecin => 'Médecin',
            self::Etudiant => 'Étudiant',
            self::Parent => 'Parent',
            self::Enseignant => 'Enseignant',
        };
    }
}
