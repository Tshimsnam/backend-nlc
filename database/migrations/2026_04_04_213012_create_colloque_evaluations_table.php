<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colloque_evaluations', function (Blueprint $table) {
            $table->id();

            // Section 1 — Infos personnelles
            $table->string('full_name')->nullable();
            $table->string('etablissement')->nullable();
            $table->string('profil')->nullable();          // étudiant, docteur, etc.
            $table->string('profil_autre')->nullable();
            $table->string('contact')->nullable();
            $table->date('date_colloque')->nullable();
            $table->string('duree_session')->nullable();

            // Section 2 — Contenu
            $table->enum('adequation_theme', ['tres_adequat','adequat','neutre','pas_vraiment','pas_du_tout'])->nullable();
            $table->text('aspects_pertinents')->nullable();
            $table->text('sujets_manquants')->nullable();

            // Section 3 — Qualité présentation
            $table->enum('clarte_presentations', ['excellente','tres_bonne','bonne','acceptable','insatisfaisante'])->nullable();
            $table->enum('maintien_attention', ['toujours','souvent','parfois','jamais'])->nullable();

            // Section 4 — Organisation
            $table->enum('organisation_generale', ['excellente','tres_bonne','bonne','acceptable','a_ameliorer'])->nullable();
            $table->enum('respect_horaires', ['toujours','la_plupart','parfois','rarement','jamais'])->nullable();
            $table->text('logistique_commentaire')->nullable();

            // Section 5 — Networking
            $table->enum('opportunites_interaction', ['beaucoup','quelques','peu','aucune'])->nullable();
            $table->text('contacts_professionnels')->nullable();

            // Section 6 — Apprentissage
            $table->text('enseignements_tires')->nullable();
            $table->text('application_enseignements')->nullable();

            // Section 7 — Évaluation globale
            $table->unsignedTinyInteger('note_globale')->nullable(); // 1-10
            $table->text('points_forts')->nullable();
            $table->text('suggestions_amelioration')->nullable();

            // Section 8 — Commentaires
            $table->text('commentaires_additionnels')->nullable();

            // Section 9 — Quiz TSA (réponses A/B/C/D)
            $table->enum('tsa_q1', ['A','B','C','D'])->nullable();
            $table->enum('tsa_q2', ['A','B','C','D'])->nullable();
            $table->enum('tsa_q3', ['A','B','C','D'])->nullable();
            $table->enum('tsa_q4', ['A','B','C','D'])->nullable();
            $table->enum('tsa_q5', ['A','B','C','D'])->nullable();

            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colloque_evaluations');
    }
};
