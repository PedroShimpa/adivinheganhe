<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        session(['indicated_by' => request()->input('ib')]);
        session(['platform' => request()->input('platform', 'web')]); 
        return Socialite::driver('google')->redirect();
    }


    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();
        
        if (!$user) {
            // Base do username (sem espaços, minúsculo)
            $usernameBase = strtolower(preg_replace('/\s+/', '', $googleUser->getName()));
            $username = $usernameBase;
            $counter = 1;
        
            // Se já existir alguém com este username, adiciona números
            while (User::where('username', $username)->exists()) {
                $username = $usernameBase . $counter;
                $counter++;
            }
        
            $user = User::create([
                'name' => $googleUser->getName(),
                'username' => $username,
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(uniqid()),
                'email_verified_at' => now(),
                'indicated_by' => session('indicated_by')
            ]);
        }

        Auth::login($user, true);

        $platform = session('platform', 'web');

        if ($platform === 'mobile') {
            $token = $user->createToken('api-token')->plainTextToken;

            // Serializa o usuário em JSON e codifica para URL
            $userJson = urlencode(json_encode([
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
            ]));

            return redirect('adivinheganhe://home?token=' . $token . '&user=' . $userJson.'&route=/home');
        }

        return redirect()->route('home');
    }
}
