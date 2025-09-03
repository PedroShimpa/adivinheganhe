<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        session(['indicated_by' => request()->input('ib')]);
        session(['platform' => request()->input('platform', 'web')]); // default web
        return Socialite::driver('google')->redirect();
    }
}
