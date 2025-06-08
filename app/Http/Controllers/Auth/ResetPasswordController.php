<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use PHPUnit\Event\Test\Passed;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use function Laravel\Prompts\password;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token){
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request){
        $request->validate([
            'token'=>'required',
            'email'=>'required|email',
            'password'=> 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function($user, $password){
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if($status == Password::PASSWORD_RESET){
            return redirect('api/login')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }
}
