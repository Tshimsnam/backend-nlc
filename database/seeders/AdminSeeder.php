<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  on verifie le rôle administrateur si pas encore là si oui on le cree
        $adminRole = Role::firstOrCreate(['name' => 'Administrateur']);
        //si l'admin existe deja
        $admin=User::where('email', 'exemple@gmail.com')->first();

        if(!$admin){
            $admin=User::create([
                'name'=> 'Admin',
                'email' => 'exemple@gmail.com',
                'password' => null,
            ]);
        }

         // le rôle administrateur est ataché directement
        $admin->roles()->attach($adminRole->id);

        //on crée le token de réinitialisation
        $token = Password::createToken($admin);

        //le lien vers la route de reinitialisation

        $resetUrl = url("/reset-password/{$token}?email={$admin->email}");

        //le mail pour la modification

        Mail::raw("Bonjour cher Admin, cliquez ici pour définir votre mot de passe: $resetUrl", function ($message) use ($admin) {
        $message->to($admin->email)->subject('Definir votre mot de passe');
        });

        echo "lien de réinitialisation envoyé à l'admin : $resetUrl\n";
    }
}
