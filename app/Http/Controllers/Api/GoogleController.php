<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $usernameBase = strtolower(preg_replace('/\s+/', '', $googleUser->getName()));
            $username = $usernameBase . rand(1, 10);
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'username' => $username,
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'user' => $user->only(['id', 'name', 'email', 'username']),
                'token' => $token
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro no login Google: ' . $e->getMessage()], 400);
        }
    }
}
