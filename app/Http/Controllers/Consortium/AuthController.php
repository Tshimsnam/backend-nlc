<?php

namespace App\Http\Controllers\Consortium;

use App\Http\Controllers\Controller;
use App\Models\Consortium\Account;
use App\Models\Consortium\Structure;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:255', 'structure_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:consortium_accounts,email',
            'password' => ['required', 'string', 'max:128', 'confirmed', PasswordRule::min(12)],
        ]);
        $account = DB::transaction(function () use ($data) {
            $structure = Structure::create(['name' => $data['structure_name']]);
            $account = $structure->accounts()->create(collect($data)->only(['name', 'email', 'password'])->all());
            $structure->application()->create(['data' => ['official_name' => $data['structure_name']]]);
            $account->sendVerification();

            return $account;
        });
        Auth::guard('consortium')->login($account);
        $request->session()->forget('password_hash_consortium');
        $request->session()->regenerate();

        return response()->json(['account' => $account, 'csrf_token' => csrf_token()], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string|max:128']);
        $account = Account::where('email', Str::lower(trim($data['email'])))->first();
        if (! $account || ! Hash::check($data['password'], $account->password)) {
            throw ValidationException::withMessages(['email' => 'Identifiants incorrects.']);
        }
        Auth::guard('consortium')->login($account);
        $request->session()->forget('password_hash_consortium');
        $request->session()->regenerate();

        return response()->json(['account' => $account, 'csrf_token' => csrf_token()]);
    }

    public function logout(Request $request)
    {
        Auth::guard('consortium')->logout();
        $request->session()->forget('password_hash_consortium');
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Vous êtes déconnecté.', 'csrf_token' => csrf_token()]);
    }

    public function me(Request $request)
    {
        return response()->json(['account' => $request->user('consortium'), 'structure' => $request->user('consortium')->structure]);
    }

    public function updateAccount(Request $request)
    {
        $account = $request->user('consortium');
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('consortium_accounts')->ignore($account->id)],
            'current_password' => 'nullable|string|max:128', 'password' => ['nullable', 'string', 'max:128', 'confirmed', PasswordRule::min(12)],
        ]);
        $changedEmail = $data['email'] !== $account->email;
        if (($changedEmail || ! empty($data['password'])) && ! Hash::check($data['current_password'] ?? '', $account->password)) {
            throw ValidationException::withMessages(['current_password' => 'Votre mot de passe actuel est nécessaire pour cette modification.']);
        }
        DB::transaction(function () use ($account, $data, $changedEmail) {
            $account->fill(collect($data)->only(['name', 'email'])->all());
            if (! empty($data['password'])) {
                $account->password = $data['password'];
                $account->remember_token = Str::random(60);
            }
            if ($changedEmail) {
                $account->email_verified_at = null;
            }
            $account->save();
            if ($changedEmail) {
                $account->sendVerification();
            }
        });
        $request->session()->regenerate();

        return response()->json(['account' => $account, 'csrf_token' => csrf_token()]);
    }

    public function resend(Request $request)
    {
        $account = $request->user('consortium');
        if (! $account->email_verified_at) {
            $account->sendVerification();
        }

        return response()->json(['message' => 'Un lien de vérification a été mis en envoi.']);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'token' => 'required|string|size:64']);
        DB::transaction(function () use ($data) {
            $account = Account::where('email', Str::lower($data['email']))->lockForUpdate()->first();
            if (! $account || ! $account->verification_token || ! $account->verification_expires_at?->isFuture() || ! hash_equals($account->verification_token, hash('sha256', $data['token']))) {
                throw ValidationException::withMessages(['token' => 'Lien invalide ou expiré. Demandez un nouveau lien depuis votre espace.']);
            }
            $account->forceFill(['email_verified_at' => now(), 'verification_token' => null, 'verification_expires_at' => null])->save();
        });

        return response()->json(['message' => 'Votre adresse e-mail est confirmée.']);
    }

    public function forgot(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);
        Password::broker('consortium')->sendResetLink(['email' => Str::lower($data['email'])]);

        return response()->json(['message' => 'Si un compte correspond à cette adresse, un lien de récupération sera envoyé.']);
    }

    public function reset(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'token' => 'required|string', 'password' => ['required', 'string', 'max:128', 'confirmed', PasswordRule::min(12)]]);
        $data['email'] = Str::lower($data['email']);
        $status = Password::broker('consortium')->reset($data, function (Account $account, string $password) {
            $account->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($account));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['token' => 'Lien invalide ou expiré. Demandez un nouveau lien.']);
        }

        return response()->json(['message' => 'Mot de passe modifié. Vous pouvez vous connecter.']);
    }
}
