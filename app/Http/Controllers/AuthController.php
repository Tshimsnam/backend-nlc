<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\User as User;
use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
       try {
         $user = UserModel::create([
            'name' =>$request->input("name"),
            'email' =>$request->input("email"),
            'password' => Hash::make($request->input("password")),
        ]);

        // Assigné le rôle
        $role = Role::where('name', $request->input("role"))->first();
        $user->roles()->attach($role);

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
       } catch (\Throwable $th) {
       return response()->json([
            'msg' => "Cet utilisateur existe deja",
        ], 500);
       }
    }

    public function login(Request $request)
    {
        $user_log=$request->input();


        $user = UserModel::where('email', $request->input("email"))->first();

        if (!$user || !Hash::check($request->input("password"), $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

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
