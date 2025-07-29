<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\AdicionaisIndicacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * TODO criar request especifico para registro e validaro cpf
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $request->validated();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'cpf' => $request->cpf,
            'whatsapp' => $request->whatsapp,
            'indicated_by' => $request->input('indicated_by')
        ]);

        event(new Registered($user));

        if (!empty($request->input('indicated_by'))) {
            $indicated = AdicionaisIndicacao::where('user_uuid',  $request->input('indicated_by'))->first();
            if (!empty($indicated)) {
                $indicated->value = $indicated->value + env('INDICATION_ADICIONAL', 5);
                $indicated->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => $request->input('indicated_by'), 'value' => env('INDICATION_ADICIONAL', 5)]);
            }
        }

        Auth::login($user);

        return redirect(route('home', absolute: false));
    }
}
