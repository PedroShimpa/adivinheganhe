<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\AdicionaisIndicacao;
use App\Models\AdivinheOMilhao\Adicionais;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Events\NewUserRegistered;
use App\Models\User;

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
            'indicated_by' => $request->input('indicated_by'),
            'fingerprint' =>  $request->input('fingerprint')
        ]);

        if (!empty($request->input('indicated_by'))) {
            $indicated = AdicionaisIndicacao::where('user_uuid',  $request->input('indicated_by'))->first();
            if (!empty($indicated)) {
                $indicated->value = $indicated->value + env('INDICATION_ADICIONAL', 5);
                $indicated->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => $request->input('indicated_by'), 'value' => env('INDICATION_ADICIONAL', 5)]);
            }
            $indicatedAdivinheOMilhao = Adicionais::where('user_uuid',  $request->input('indicated_by'))->first();
            if (!empty($indicatedAdivinheOMilhao)) {
                $indicatedAdivinheOMilhao->value = $indicatedAdivinheOMilhao->value + 1;
                $indicatedAdivinheOMilhao->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => $request->input('indicated_by'), 'value' => 1]);
            }
        }

        Auth::login($user);

        // Check for high registration rate
        $recentRegistrations = User::where('created_at', '>=', now()->subMinutes(20))->count();
        if ($recentRegistrations >= 10) {
            $cacheKey = 'high_registration_alert_sent';
            if (!Cache::has($cacheKey)) {
                $admins = User::where('is_admin', true)->pluck('email')->toArray();
                if (!empty($admins)) {
                    \Mail::to($admins)->send(new \App\Mail\HighRegistrationAlertMail());
                    Cache::put($cacheKey, true, now()->addHour());
                }
            }
        }

        return redirect(route('home', absolute: false));
    }
}
