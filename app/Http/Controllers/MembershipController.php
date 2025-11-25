<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Mail\MembershipPurchaseAdminMail;
use App\Mail\MembershipWelcomeMail;
use App\Models\User;
use App\Models\Pagamentos;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Exceptions\MPApiException;


class MembershipController extends Controller
{
    public function index()
    {
        Log::info('Membership page accessed', [
            'user_id' => Auth::id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return view('seja_membro');
    }

    public function buyVip(Request $request)
    {
        $valor = config('app.membership_value');
        $desc = "Compra de VIP mensal";

        try {
            $pag = Pagamentos::create([
                'user_id' => auth()->id(),
                'value' => $valor,
                'desc' => $desc,
            ]);
            MercadoPagoConfig::setAccessToken(env("MERCADO_PAGO_ACCESS_TOKEN"));

            $client = new PaymentClient();
            $request_options = new RequestOptions();
            $uniqueKey = now()->timestamp . $pag->id . md5(now()->timestamp);
            $request_options->setCustomHeaders(["X-Idempotency-Key: {$uniqueKey}"]);

            $payment = $client->create([
                "transaction_amount" => $valor,
                "description" => $desc,
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => auth()->user()->email,
                    "first_name" => auth()->user()->name,
                    "last_name" => "",
                    "identification" => [
                        "type" => "CPF",
                        "number" => auth()->user()->cpf
                    ]
                ]
            ], $request_options);

            $pag->payment_status = $payment->status;
            $pag->payment_id = $payment->id;
            $pag->save();

            if ($payment->status == 'pending') {
                return response()->json([
                    'success' => true,
                    'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
                    'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64
                ]);
            } else {
                return response()->json(['success' => false]);
            }
        } catch (MPApiException $e) {
            Log::error("Status code: " . $e->getApiResponse()->getStatusCode() . "\n");
            Log::error($e->getApiResponse()->getContent());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function createCheckoutSession(Request $request)
    {
        try {
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

            Log::info('Stripe checkout session created', [
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'amount' => config('app.membership_value')
            ]);

            return response()->json(['id' => $session->id]);
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe checkout session', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Erro ao processar pagamento. Tente novamente.'], 500);
        }
    }

    public function success(Request $request)
    {
        try {
            $sessionId = $request->get('session_id');

            if (!$sessionId) {
                Log::warning('Membership success called without session_id', ['user_id' => Auth::id()]);
                return redirect()->route('membership.index')->with('error', 'Sessão de pagamento não encontrada.');
            }

            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $user = Auth::user();
                $user->is_vip = true;
                $user->membership_expires_at = now()->addMonth();
                $user->save();

                Log::info('User successfully upgraded to VIP', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'stripe_payment_id' => $session->payment_intent
                ]);

                // Send WhatsApp message to community
                try {
                    $API_BASE = env('NOTIFICACAO_API_BASE');
                    $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
                    $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
                    $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

                    $tokenRes = Http::post($TOKEN_ENDPOINT);

                    if ($tokenRes->successful() && $tokenRes->json('status') === 'success') {
                        $token = $tokenRes->json('token');
                        $headers = ["Authorization" => "Bearer $token"];

                        $mensagem = "🌟 Parabéns, {$user->username}! Você agora faz parte do nosso grupo VIP!\nObrigado por apoiar o projeto — aproveite seus novos privilégios! 👑";

                        $payload = [
                            "phone" => $PHONE_ID,
                            "isGroup" => false,
                            "isNewsletter" => true,
                            "isLid" => false,
                            "message" => $mensagem,
                        ];

                        $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);

                        if (!$resp->successful()) {
                            Log::error("Erro ao enviar mensagem WhatsApp para VIP: " . $resp->body());
                        }
                    } else {
                        Log::error("Erro ao gerar token para WhatsApp VIP: " . $tokenRes->body());
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao enviar notificação WhatsApp para VIP: " . $e->getMessage());
                }

                // Send welcome email to user
                Mail::to($user->email)->queue((new MembershipWelcomeMail($user)));

                // Notify admins
                $admins = User::where('is_admin', 'S')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user)));
                }

                return redirect()->route('membership.result')->with('success', 'Bem-vindo ao clube VIP!');
            }

            Log::warning('Payment not completed', [
                'user_id' => Auth::id(),
                'session_id' => $sessionId,
                'payment_status' => $session->payment_status
            ]);

            return redirect()->route('membership.result')->with('error', 'Pagamento não foi processado.');
        } catch (\Exception $e) {
            Log::error('Error processing membership success', [
                'user_id' => Auth::id(),
                'session_id' => $request->get('session_id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('membership.result')->with('error', 'Erro ao processar pagamento. Entre em contato com o suporte.');
        }
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();

        try {
            $event = json_decode($payload, true);

            if (!$event) {
                Log::error('Stripe webhook invalid JSON payload', [
                    'payload_length' => strlen($payload)
                ]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }
        } catch (\Exception $e) {
            Log::error('Stripe webhook JSON decode failed', [
                'payload_length' => strlen($payload),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('Stripe webhook received', [
            'event_type' => $event['type'] ?? 'unknown',
            'event_id' => $event['id'] ?? 'unknown'
        ]);

        if (($event['type'] ?? null) === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? null;

            if (!$session) {
                Log::error('Stripe webhook missing session data');
                return response()->json(['error' => 'Missing session data'], 400);
            }

            if (($session['payment_status'] ?? null) === 'paid') {
                $userId = $session['metadata']['user_id'] ?? null;

                if (!$userId) {
                    Log::error('Stripe webhook missing user_id in metadata', [
                        'session_id' => $session['id'] ?? 'unknown',
                        'metadata' => $session['metadata'] ?? []
                    ]);
                    return response()->json(['error' => 'Missing user metadata'], 400);
                }

                $user = \App\Models\User::find($userId);

                if ($user) {
                    $user->is_vip = true;
                    $user->membership_expires_at = now()->addMonth();
                    $user->save();

                    Log::info('User upgraded to VIP via webhook', [
                        'user_id' => $userId,
                        'session_id' => $session['id'] ?? 'unknown',
                        'stripe_payment_id' => $session['payment_intent'] ?? 'unknown'
                    ]);

                    // Send WhatsApp message to community
                    try {
                        $API_BASE = env('NOTIFICACAO_API_BASE');
                        $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
                        $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
                        $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

                        $tokenRes = Http::post($TOKEN_ENDPOINT);

                        if ($tokenRes->successful() && $tokenRes->json('status') === 'success') {
                            $token = $tokenRes->json('token');
                            $headers = ["Authorization" => "Bearer $token"];
                            $mensagem = "🌟 Parabéns, {$user->username}!\nAgora você faz parte do grupo VIP — privilégio dos melhores! 👑";

                            $payload = [
                                "phone" => $PHONE_ID,
                                "isGroup" => false,
                                "isNewsletter" => true,
                                "isLid" => false,
                                "message" => $mensagem,
                            ];

                            $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);

                            if (!$resp->successful()) {
                                Log::error("Erro ao enviar mensagem WhatsApp para VIP: " . $resp->body());
                            }
                        } else {
                            Log::error("Erro ao gerar token para WhatsApp VIP: " . $tokenRes->body());
                        }
                    } catch (\Exception $e) {
                        Log::error("Erro ao enviar notificação WhatsApp para VIP: " . $e->getMessage());
                    }

                    // Send welcome email to user
                    Mail::to($user->email)->queue((new MembershipWelcomeMail($user)));

                    // Notify admins
                    $admins = User::where('is_admin', 'S')->get();
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user)));
                    }
                } else {
                    Log::error('User not found for VIP upgrade', [
                        'user_id' => $userId,
                        'session_id' => $session['id'] ?? 'unknown'
                    ]);
                }
            } else {
                Log::warning('Stripe webhook payment not completed', [
                    'session_id' => $session['id'] ?? 'unknown',
                    'payment_status' => $session['payment_status'] ?? 'unknown'
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function checkPaymentStatus(Request $request)
    {
        $paymentId = $request->input('payment_id');

        if (!$paymentId || !is_numeric($paymentId)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payment ID'], 400);
        }

        $paymentId = (int) $paymentId;

        try {
            MercadoPagoConfig::setAccessToken(env("MERCADO_PAGO_ACCESS_TOKEN"));
            $client = new PaymentClient();
            $payment = $client->get($paymentId);

            if ($payment->status == 'approved') {
                // Upgrade user to VIP
                $pagamento = Pagamentos::where('payment_id', $paymentId)->first();
                if ($pagamento && !$pagamento->processed) {
                    $user = $pagamento->user;
                    $user->is_vip = true;
                    $user->membership_expires_at = now()->addMonth();
                    $user->save();

                    $pagamento->processed = true;
                    $pagamento->save();

                    Log::info('User upgraded to VIP via PIX', [
                        'user_id' => $user->id,
                        'payment_id' => $paymentId
                    ]);

                    // Send WhatsApp message to community
                    try {
                        $API_BASE = env('NOTIFICACAO_API_BASE');
                        $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
                        $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
                        $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

                        $tokenRes = Http::post($TOKEN_ENDPOINT);

                        if ($tokenRes->successful() && $tokenRes->json('status') === 'success') {
                            $token = $tokenRes->json('token');
                            $headers = ["Authorization" => "Bearer $token"];

                            $mensagem = "🌟 Parabéns, {$user->username}!\nAgora você faz parte do grupo VIP — privilégio dos melhores! 👑";

                            $payload = [
                                "phone" => $PHONE_ID,
                                "isGroup" => false,
                                "isNewsletter" => true,
                                "isLid" => false,
                                "message" => $mensagem,
                            ];

                            $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);

                            if (!$resp->successful()) {
                                Log::error("Erro ao enviar mensagem WhatsApp para VIP: " . $resp->body());
                            }
                        } else {
                            Log::error("Erro ao gerar token para WhatsApp VIP: " . $tokenRes->body());
                        }
                    } catch (\Exception $e) {
                        Log::error("Erro ao enviar notificação WhatsApp para VIP: " . $e->getMessage());
                    }

                    // Send welcome email to user
                    Mail::to($user->email)->queue((new MembershipWelcomeMail($user)));

                    // Notify admins
                    $admins = User::where('is_admin', 'S')->get();
                    foreach ($admins as $admin) {
                        Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user)));
                    }
                }
            }

            return response()->json(['status' => $payment->status]);
        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json(['status' => 'error']);
        }
    }

    public function result()
    {
        return view('membership_result');
    }
}
