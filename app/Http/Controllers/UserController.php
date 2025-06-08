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

class UserController extends Controller
{

    public function index(Request $request)
    {
        // Récupérer tous les users avec leurs rôles
        $users = \App\Models\User::with('roles')->get();

        // Optionnel : Mapper la réponse pour avoir un format API clean
        $data = $users->map(function ($user) {
            return [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => $user->phone,
                'status'  => $user->status,
                'roles'   => $user->roles->pluck('name'), // Liste des rôles par nom
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'users' => $data,
            'total' => $data->count(),
        ]);
    }

}
