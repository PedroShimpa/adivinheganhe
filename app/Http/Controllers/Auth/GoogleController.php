<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterExtraUserRequest;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        session(['indicated_by' => request()->input('ib')]);
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (!$user) {
            $usernameBase = strtolower(preg_replace('/\s+/', '', $googleUser->getName()));
            $username = $usernameBase . rand(1, 10);

            $user = User::create([
                'name' => $googleUser->getName(),
                'username' => $username,
                'email' => $googleUser->getEmail(),
                'password' => bcrypt(uniqid()),
                'email_verified_at' => now(),
                'indicated_by' => session('indicated_by')
            ]);

            Auth::login($user);

            return redirect()->route('home');
        }

        Auth::login($user);

        return redirect()->route('home');
    }

    public function showExtraForm()
    {
        $socialUser = session('social_user');
        if (!$socialUser) return redirect()->route('home');

        return view('auth.register_extra', ['user' => $socialUser]);
    }

    public function storeExtraForm(RegisterExtraUserRequest $request)
    {

        $socialUser = session('social_user');
        if (!$socialUser) return redirect()->route('home');

        $user = User::create([
            'name' => $socialUser['name'],
            'username' => $request->username,
            'email' => $socialUser['email'],
            'password' => bcrypt(uniqid()),
            'email_verified_at' => now(),
            'cpf' => $request->cpf,
            'whatsapp' => $request->whatsapp,
            'fingerprint' => $request->input('fingerprint'),
            'indicated_by' => session('indicated_by')
        ]);

        session()->forget('social_user');

        Auth::login($user);

        return redirect()->route('home');
    }
}
