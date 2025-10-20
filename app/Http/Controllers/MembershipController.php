<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Webhook;

class MembershipController extends Controller
{
    public function index()
    {
        return view('seja_membro');
    }

    public function createCheckoutSession(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'brl',
                    'product_data' => [
                        'name' => 'Membro VIP',
                        'description' => 'Acesso a adivinhações exclusivas e benefícios especiais',
                    ],
                    'unit_amount' => config('app.membership_value') * 100, // in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('membership.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('membership.index'),
            'metadata' => [
                'user_id' => Auth::id(),
            ],
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('membership.index')->with('error', 'Sessão de pagamento não encontrada.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $session = Session::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            $user = Auth::user();
            $user->is_vip = true;
            $user->membership_expires_at = now()->addMonth();
            $user->save();

            return redirect()->route('home')->with('success', 'Bem-vindo ao clube VIP!');
        }

        return redirect()->route('membership.index')->with('error', 'Pagamento não foi processado.');
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                config('services.stripe.webhook.secret')
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            if ($session->payment_status === 'paid') {
                $userId = $session->metadata->user_id;
                $user = \App\Models\User::find($userId);

                if ($user) {
                    $user->is_vip = true;
                    $user->membership_expires_at = now()->addMonth();
                    $user->save();
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
