<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

    public function mobileLoginToken(Request $request)
    {
        $idToken = $request->input('id_token');
        if (!$idToken) return response()->json(['error' => 'id_token obrigatório'], 400);

        $resp = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $idToken
        ]);

        if ($resp->failed()) return response()->json(['error' => 'idToken inválido'], 401);

        $payload = $resp->json();
        $email = $payload['email'];
        $name = $payload['name'] ?? 'Usuário';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => strtolower(preg_replace('/\s+/', '', $name)) . rand(1, 10),
                'password' => bcrypt(uniqid()),
                'email_verified_at' => now()
            ]
        );

        $loginToken = bin2hex(random_bytes(16));
        Cache::put('login_token:' . $loginToken, $user->id, now()->addMinutes(10));

        return response()->json(['login_token' => $loginToken]);
    }

    public function loginWithToken(Request $request)
    {
        $token = $request->query('token');
        if (!$token) abort(401);

        $userId = Cache::pull('login_token:' . $token);
        if (!$userId) abort(401);

        $user = User::find($userId);
        if (!$user) abort(401);

        Auth::login($user);
        return redirect('/'); 
    }
}
