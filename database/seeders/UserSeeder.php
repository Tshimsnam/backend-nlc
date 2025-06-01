<?php 

namespace Database\Seeders; 

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Notifications\PasswordSetNotification;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Création utilisateur admin sans mot de passe
        $user = User::create([
            'name' => 'Admin Principal',
            'email' => 'choupole13@gmail.com',
            'password' => '', // vide ou null, selon ton modèle
        ]);

        // Assignation du rôle
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user->roles()->attach($adminRole);
        }

        // Générer token et l’insérer
        $token = Str::uuid();
        DB::table('password_set_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addDays(7),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Envoyer le mail avec le token
        $user->notify(new PasswordSetNotification($token));
    }
}
