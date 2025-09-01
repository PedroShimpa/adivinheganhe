<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['uuid'] = Str::uuid()->toString();
        if ($request->input('token_push_notification')) {
            $user['token_push_notification'] = $request->input('token_push_notification');
        }
        $user = User::create($data);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Registro realizado com sucesso!',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login user and return token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if ($request->input('token_push_notification')) {
            $user->update(['token_push_notification' => $request->input('token_push_notification')]);
        }
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login realizado com sucesso!',
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso!']);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            ['email' => $request->email]
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Link de redefinição de senha enviado!']);
        } else {
            return response()->json(['message' => 'Não foi possível enviar o link de redefinição.'], 500);
        }
    }
}
