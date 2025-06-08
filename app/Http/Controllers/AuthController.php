<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User; // <-- LA BONNE IMPORTATION
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendPasswordSetMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role'  => 'required|exists:roles,name',
        ]);

        try {
            // Création utilisateur SANS mot de passe
            $user = User::create([
                'name'     => $request->input('name'),
                'email'    => $request->input('email'),
                'password' => '', // pas de mot de passe initial
                'telephone'    => $request->input('phone'),
                'statut'   => $request->input('status', 'active'), // facultatif selon ton modèle
            ]);

            // Assignation du rôle
            $role = Role::where('name', $request->input('role'))->first();
            $user->roles()->attach($role);

            // Générer le token de set-password
            $token = Str::uuid();
            DB::table('password_set_tokens')->insert([
                'user_id'    => $user->id,
                'token'      => $token,
                'expires_at' => now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Générer le lien de set-password
            $frontendUrl = env('FRONTEND_NLC', 'http://localhost:3000');
            $url = "{$frontendUrl}/set-password?token={$token}";

            // Envoi du mail via Job
            SendPasswordSetMail::dispatch($user, $url);

            return response()->json([
                'user'  => $user,
                'msg'   => 'Utilisateur créé et mail envoyé',
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'msg' => $th->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $user = UserModel::where('email', $request->input("email"))->first();

        if (!$user || !Hash::check($request->input("password"), $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        // Charge explicitement les rôles (sauf si tu as mis $with = ['roles'] dans le modèle)
        $user->load('roles');

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }
}
