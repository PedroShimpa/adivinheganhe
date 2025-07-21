<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdicionaisIndicacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', Rules\Password::defaults()],
            'cpf' => ['required', 'string', 'max:20', 'unique:users,cpf'],
            'whatsapp' => ['nullable', 'string', 'max:15', 'unique:users,whatsapp', 'regex:/^\(?\d{2}\)?\s?\d{4,5}-?\d{4}$/'],
            'indicated_by' => ['nullable', 'string']
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'cpf' => $request->cpf,
            'whatsapp' => $request->whatsapp,
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
