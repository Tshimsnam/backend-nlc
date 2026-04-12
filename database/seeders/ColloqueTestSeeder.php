<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColloqueTestSeeder extends Seeder
{
    public function run(): void
    {
        $participants = [
            [
                // S1
                'full_name'                 => 'Dr. Marie Kabila',
                'etablissement'             => 'Université de Kinshasa',
                'profil'                    => 'Docteur(e)',
                'profil_autre'              => null,
                'contact'                   => '+243 81 234 5678',
                'date_colloque'             => '2026-04-05',
                'duree_session'             => '3h',
                // S2
                'adequation_theme'          => 'tres_adequat',
                'aspects_pertinents'        => 'Les présentations sur le diagnostic précoce étaient très pertinentes. J\'ai particulièrement apprécié les études de cas présentées par les intervenants.',
                'sujets_manquants'          => 'J\'aurais aimé voir plus de contenu sur les thérapies comportementales adaptées au contexte africain.',
                // S3
                'clarte_presentations'      => 'excellente',
                'maintien_attention'        => 'toujours',
                // S4
                'organisation_generale'     => 'tres_bonne',
                'respect_horaires'          => 'la_plupart',
                'logistique_commentaire'    => 'L\'accueil était chaleureux. Les pauses café bien organisées. Le matériel de projection fonctionnait parfaitement.',
                // S5
                'opportunites_interaction'  => 'beaucoup',
                'contacts_professionnels'   => 'J\'ai échangé avec plusieurs professeurs de l\'UNIKIN et établi des contacts avec une ONG spécialisée en autisme.',
                // S6
                'enseignements_tires'       => 'J\'ai appris que le TSA est sous-diagnostiqué en RDC faute de spécialistes formés. Les outils de dépistage précoce sont accessibles et peu coûteux.',
                'application_enseignements' => 'Je vais intégrer les outils de dépistage dans mes consultations pédiatriques et former mon équipe.',
                // S7
                'note_globale'              => 9,
                'points_forts'             => 'Qualité des intervenants, diversité des thèmes, ambiance inclusive.',
                'suggestions_amelioration'  => 'Prévoir des sous-titres pour les présentations en anglais. Augmenter le temps de questions-réponses.',
                // S8
                'commentaires_additionnels' => 'Félicitations à l\'équipe NLC pour cette initiative. Continuez ce travail remarquable !',
                // S9
                'tsa_q1' => 'B',
                'tsa_q2' => 'B',
                'tsa_q3' => 'A',
                'tsa_q4' => 'B',
                'tsa_q5' => 'C',
            ],
            [
                'full_name'                 => 'Jean-Pierre Mwamba',
                'etablissement'             => 'École Primaire Sainte-Marie',
                'profil'                    => 'Enseignant(e)',
                'profil_autre'              => null,
                'contact'                   => '+243 99 876 5432',
                'date_colloque'             => '2026-04-05',
                'duree_session'             => '3h',
                'adequation_theme'          => 'adequat',
                'aspects_pertinents'        => 'Les stratégies d\'inclusion en classe ordinaire m\'ont beaucoup aidé.',
                'sujets_manquants'          => 'Comment gérer les crises en classe avec peu de ressources.',
                'clarte_presentations'      => 'bonne',
                'maintien_attention'        => 'souvent',
                'organisation_generale'     => 'bonne',
                'respect_horaires'          => 'parfois',
                'logistique_commentaire'    => 'La salle était un peu petite pour le nombre de participants.',
                'opportunites_interaction'  => 'quelques',
                'contacts_professionnels'   => 'J\'ai rencontré un orthophoniste qui peut intervenir dans mon école.',
                'enseignements_tires'       => 'Les enfants autistes ont des forces uniques qu\'il faut valoriser en classe.',
                'application_enseignements' => 'Je vais adapter mes méthodes pédagogiques et sensibiliser mes collègues.',
                'note_globale'              => 7,
                'points_forts'             => 'Témoignages de parents très touchants. Intervenants accessibles.',
                'suggestions_amelioration'  => 'Plus d\'ateliers pratiques pour les enseignants.',
                'commentaires_additionnels' => 'Merci pour cette journée enrichissante.',
                'tsa_q1' => 'A',
                'tsa_q2' => 'D',
                'tsa_q3' => 'A',
                'tsa_q4' => 'A',
                'tsa_q5' => 'C',
            ],
            [
                'full_name'                 => 'Amina Diallo',
                'etablissement'             => 'Association Parents d\'Enfants Autistes',
                'profil'                    => 'Parent',
                'profil_autre'              => null,
                'contact'                   => '+243 82 111 2233',
                'date_colloque'             => '2026-04-05',
                'duree_session'             => '3h',
                'adequation_theme'          => 'tres_adequat',
                'aspects_pertinents'        => 'Enfin une conférence qui parle de nos enfants avec respect et dignité.',
                'sujets_manquants'          => 'Les droits légaux des enfants autistes en RDC.',
                'clarte_presentations'      => 'tres_bonne',
                'maintien_attention'        => 'toujours',
                'organisation_generale'     => 'excellente',
                'respect_horaires'          => 'toujours',
                'logistique_commentaire'    => 'Tout était parfait. Merci pour la garderie mise en place.',
                'opportunites_interaction'  => 'beaucoup',
                'contacts_professionnels'   => 'J\'ai rejoint un groupe de parents pour partager nos expériences.',
                'enseignements_tires'       => 'Mon enfant n\'est pas seul. Il existe des ressources et des communautés de soutien.',
                'application_enseignements' => 'Je vais rejoindre l\'association NLC et participer aux prochains événements.',
                'note_globale'              => 10,
                'points_forts'             => 'L\'humanité et la bienveillance de toute l\'équipe.',
                'suggestions_amelioration'  => 'Organiser des sessions régionales pour toucher plus de familles.',
                'commentaires_additionnels' => 'Vous changez des vies. Merci du fond du cœur.',
                'tsa_q1' => 'B',
                'tsa_q2' => 'B',
                'tsa_q3' => 'A',
                'tsa_q4' => 'B',
                'tsa_q5' => 'C',
            ],
            [
                'full_name'                 => 'Étudiant Anonyme',
                'etablissement'             => 'ISTM Kinshasa',
                'profil'                    => 'Étudiant(e)',
                'profil_autre'              => null,
                'contact'                   => null,
                'date_colloque'             => '2026-04-05',
                'duree_session'             => '3h',
                'adequation_theme'          => 'neutre',
                'aspects_pertinents'        => 'Les statistiques sur la prévalence du TSA en Afrique.',
                'sujets_manquants'          => null,
                'clarte_presentations'      => 'acceptable',
                'maintien_attention'        => 'parfois',
                'organisation_generale'     => 'bonne',
                'respect_horaires'          => 'parfois',
                'logistique_commentaire'    => null,
                'opportunites_interaction'  => 'peu',
                'contacts_professionnels'   => null,
                'enseignements_tires'       => 'Le TSA n\'est pas une maladie mentale.',
                'application_enseignements' => 'Sensibiliser mes camarades de classe.',
                'note_globale'              => 6,
                'points_forts'             => 'Bonne organisation générale.',
                'suggestions_amelioration'  => 'Plus de supports visuels.',
                'commentaires_additionnels' => null,
                'tsa_q1' => 'D',
                'tsa_q2' => 'D',
                'tsa_q3' => 'B',
                'tsa_q4' => 'D',
                'tsa_q5' => 'A',
            ],
            [
                'full_name'                 => 'Prof. Celestin Ngoy',
                'etablissement'             => 'Université Protestante au Congo',
                'profil'                    => 'Professeur(e)',
                'profil_autre'              => null,
                'contact'                   => 'c.ngoy@upc.cd',
                'date_colloque'             => '2026-04-05',
                'duree_session'             => '3h',
                'adequation_theme'          => 'tres_adequat',
                'aspects_pertinents'        => 'La dimension neurologique du TSA présentée de façon accessible.',
                'sujets_manquants'          => 'Recherches locales sur le TSA en RDC.',
                'clarte_presentations'      => 'excellente',
                'maintien_attention'        => 'toujours',
                'organisation_generale'     => 'tres_bonne',
                'respect_horaires'          => 'la_plupart',
                'logistique_commentaire'    => 'Excellente logistique. Repas de qualité.',
                'opportunites_interaction'  => 'beaucoup',
                'contacts_professionnels'   => 'Collaboration envisagée avec NLC pour un module TSA dans notre cursus.',
                'enseignements_tires'       => 'Le diagnostic clinique est la seule méthode validée. Pas de test sanguin.',
                'application_enseignements' => 'Intégrer un module TSA dans le cursus de psychologie.',
                'note_globale'              => 9,
                'points_forts'             => 'Expertise des intervenants, documentation fournie.',
                'suggestions_amelioration'  => 'Publier les actes du colloque.',
                'commentaires_additionnels' => 'Initiative à pérenniser. Bravo à NLC.',
                'tsa_q1' => 'B',
                'tsa_q2' => 'B',
                'tsa_q3' => 'A',
                'tsa_q4' => 'B',
                'tsa_q5' => 'C',
            ],
        ];

        foreach ($participants as $p) {
            DB::table('colloque_evaluations')->insert(array_merge($p, [
                'ip_hash'    => hash('sha256', '192.168.1.' . rand(1, 254)),
                'created_at' => now()->subMinutes(rand(5, 120)),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('✅ 5 évaluations de test insérées.');
    }
}
