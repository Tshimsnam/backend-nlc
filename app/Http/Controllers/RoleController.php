<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // Lister tous les rôles
    public function index()
    {
        return response()->json(Role::all());
    }

    // Créer un nouveau rôle
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name'
        ]);

        $role = Role::create($validated);

        return response()->json($role, 201);
    }

    // Afficher un rôle précis
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    // Mettre à jour un rôle
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id
        ]);

        $role->update($validated);

        return response()->json($role);
    }

    // Supprimer un rôle
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(null, 204);
    }

    // Récupérer les utilisateurs d'un rôle spécifique
    public function users($id)
    {
        $role = Role::findOrFail($id);
        $users = $role->users()->get();

        return response()->json([
            'role' => $role,
            'users' => $users,
            'total' => $users->count()
        ]);
    }
}
