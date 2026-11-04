<?php

namespace App\Services\Orientation;

use App\Models\ConsultationRequest;
use App\Models\Orientation\AccessLink;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Professional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Access
{
    public function issue(string $type, Contact|Professional $actor, string $purpose = 'login', ?ConsultationRequest $c = null): void
    {
        $token = Str::random(64);
        $expires = now()->addMinutes(config('orientation.link_minutes'));
        if ($purpose === 'login') {
            AccessLink::where(['actor_type' => $type, 'actor_id' => $actor->id, 'purpose' => 'login'])->whereNull('used_at')->update(['used_at' => now()]);
        } else {
            AccessLink::where(['purpose' => $purpose, 'consultation_request_id' => $c?->id])->whereNull('used_at')->update(['used_at' => now()]);
        }
        AccessLink::create(['token_hash' => hash('sha256', $token), 'email_hash' => hash('sha256', $actor->email), 'actor_type' => $type, 'actor_id' => $actor->id, 'purpose' => $purpose, 'consultation_request_id' => $c?->id, 'expires_at' => $expires]);
        $path = $type === 'professional' ? '/espace-professionnel' : '/espace-famille';
        app(Notices::class)->send($type, $actor->id, $actor->email, $purpose === 'guardian' ? 'Autorisation du représentant légal' : 'Votre lien de connexion',
            $purpose === 'guardian' ? 'Une demande d’orientation pour un mineur vous désigne comme représentant légal. Ouvrez votre espace pour examiner et accepter ou refuser cette démarche. Aucune séance ne sera proposée avant votre autorisation.' : 'Utilisez ce lien personnel pour vous connecter à votre espace NLC. Il est valable une seule fois pendant 30 minutes.',
            Settings::url($path).'#token='.$token, $expires);
    }

    public function consume(Request $request, string $token): array
    {
        return DB::transaction(function () use ($request, $token) {
            $link = AccessLink::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            if (! $link || $link->used_at || $link->expires_at->isPast()) {
                throw ValidationException::withMessages(['token' => 'Ce lien est invalide, déjà utilisé ou expiré. Demandez-en un nouveau.']);
            }
            $actor = $link->actor_type === 'family' ? Contact::find($link->actor_id) : Professional::find($link->actor_id);
            abort_unless($actor && $actor->active && hash_equals($link->email_hash, hash('sha256', $actor->email)) && ($link->actor_type === 'family' || $actor->validated_at), 403);
            $link->update(['used_at' => now()]);
            $request->session()->regenerate();
            $request->session()->put('orientation_actor', ['type' => $link->actor_type, 'id' => $actor->id, 'email' => $actor->email, 'expires' => now()->addHours(config('orientation.session_hours'))->timestamp]);

            return ['actor' => ['type' => $link->actor_type, 'id' => $actor->id, 'name' => $actor->name, 'email' => $actor->email, 'timezone' => $actor instanceof Professional ? $actor->timezone : null], 'request_id' => $link->consultation_request_id, 'csrf_token' => csrf_token()];
        });
    }

    public static function actor(Request $request): array
    {
        $session = $request->session()->get('orientation_actor');
        abort_unless(is_array($session) && ($session['expires'] ?? 0) > now()->timestamp, 401);
        $type = $session['type'] ?? null;
        abort_unless(in_array($type, ['family', 'professional'], true), 401);
        $actor = $type === 'family' ? Contact::find($session['id']) : Professional::find($session['id']);
        abort_unless($actor && $actor->active && hash_equals($actor->email, (string) ($session['email'] ?? '')) && ($type === 'family' || $actor->validated_at), 401);
        if ($request->header('X-Orientation-Actor') && $request->header('X-Orientation-Actor') !== $type.':'.$actor->id) {
            abort(409, 'Le compte connecté a changé dans un autre onglet. Actualisez votre espace avant de poursuivre.');
        }

        return [$type, $actor];
    }

    public static function family(Request $request): Contact
    {
        [$type,$actor] = self::actor($request);
        abort_unless($type === 'family', 403);

        return $actor;
    }

    public static function fullFamily(Contact $actor, ConsultationRequest $c): bool
    {
        return in_array($c->authority_status, ['declared', 'approved'], true) && ((int) $c->guardian_contact_id === $actor->id || (int) $c->orientation_contact_id === $actor->id);
    }

    public static function requireFamily(Request $request, ConsultationRequest $c, bool $full = true): Contact
    {
        $actor = self::family($request);
        abort_unless($full ? self::fullFamily($actor, $c) : ((int) $c->orientation_contact_id === $actor->id || (int) $c->guardian_contact_id === $actor->id), 404);

        return $actor;
    }
}
