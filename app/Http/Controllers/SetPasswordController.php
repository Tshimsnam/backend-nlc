<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SetPasswordController extends Controller
{
    public function setPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed', // ajoute un champ 'password_confirmation' côté frontend
        ]);

        $tokenRow = DB::table('password_set_tokens')->where('token', $request->token)->first();

        if (!$tokenRow) {
            return response()->json(['message' => 'Token invalide.'], 422);
        }

        if (Carbon::parse($tokenRow->expires_at)->isPast()) {
            return response()->json(['message' => 'Le lien a expiré.'], 410);
        }

        $user = User::find($tokenRow->user_id);
        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->password = $request->password; // hash auto via 

        if (is_null($user->email_verified_at)) {
            $user->email_verified_at = now();
        }
        
        $user->save();

        // Supprimer le token
        DB::table('password_set_tokens')->where('token', $request->token)->delete();

        return response()->json(['message' => 'Mot de passe défini avec succès !'], 200);
    }
}

