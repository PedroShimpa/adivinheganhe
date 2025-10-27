<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UnsubscribeController extends Controller
{
    public function unsubscribe(Request $request, $userId, $token)
    {
        $user = User::findOrFail($userId);

        // Verify token (simple hash of user email for security)
        $expectedToken = hash('sha256', $user->email . env('APP_KEY'));

        if (!hash_equals($expectedToken, $token)) {
            abort(403, 'Invalid token');
        }

        // Opt out the user
        $user->update(['email_opt_out' => true]);

        return view('emails.unsubscribed', ['user' => $user]);
    }
}
