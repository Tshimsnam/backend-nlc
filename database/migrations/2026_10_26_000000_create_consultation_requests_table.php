<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('access_token', 64)->unique();   // lien sécurisé "Compléter mon dossier"
            $table->string('status', 30)->default('nouvelle')->index();

            // --- Parent / responsable
            $table->string('parent_nom');
            $table->string('parent_prenom');
            $table->string('parent_lien', 30);               // mere, pere, tuteur, autre
            $table->string('parent_lien_autre')->nullable();
            $table->string('pays', 100);
            $table->string('ville', 100);
            $table->string('fuseau_horaire', 64);            // ex. Europe/Paris
            $table->string('email');
            $table->string('whatsapp', 30);
            $table->string('telephone', 30)->nullable();
            $table->string('langue_seance', 30);
            $table->string('contact2_nom')->nullable();
            $table->string('contact2_telephone', 30)->nullable();
            $table->string('source_connaissance', 50)->nullable();

            // --- Enfant
            $table->string('enfant_nom');
            $table->string('enfant_postnom')->nullable();
            $table->string('enfant_prenom');
            $table->string('enfant_sexe', 1);                // F, M
            $table->date('enfant_date_naissance');
            $table->string('langues_maison')->nullable();
            $table->boolean('scolarise')->nullable();
            $table->string('type_ecole', 30)->nullable();    // ordinaire, specialisee
            $table->string('classe', 50)->nullable();
            $table->string('niveau_langage', 30)->nullable();

            // --- Situation (version parent du questionnaire TSA)
            $table->text('motif');
            $table->string('age_premieres_difficultes', 100)->nullable();
            $table->json('diagnostics_anterieurs')->nullable(); // { types: [], autre, par_qui, quand }
            $table->json('suivis')->nullable();                 // [orthophonie, psychomotricite, ...]
            $table->string('antecedents_familiaux', 20)->nullable(); // oui, non, ne_sait_pas
            $table->text('naissance_particuliere')->nullable();
            $table->text('traitement_actuel')->nullable();
            $table->json('observations')->nullable();           // { regard: souvent|parfois|jamais, ... }
            $table->text('attentes')->nullable();

            // --- Rendez-vous souhaité
            $table->json('creneaux');                           // [{ start: ISO UTC }]
            $table->string('plateforme', 20);                   // zoom, meet, indifferent
            $table->string('participants', 30)->nullable();     // parents, parents_enfant

            // --- Documents et consentements
            $table->json('documents')->nullable();
            $table->boolean('consentement_donnees')->default(false);
            $table->boolean('engagement_avis_psychiatre')->default(false);
            $table->boolean('acceptation_conditions')->default(false);
            $table->timestamp('consented_at')->nullable();

            // --- Traitement par le secrétariat (phase 2)
            $table->string('professionnel_assigne')->nullable();
            $table->timestamp('seance_programmee_at')->nullable();
            $table->string('lien_visio')->nullable();
            $table->decimal('prix', 10, 2)->nullable();          // null/0 = gratuit
            $table->string('statut_paiement', 20)->nullable();
            $table->text('notes_internes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
