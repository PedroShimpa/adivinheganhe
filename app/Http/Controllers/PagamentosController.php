<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\DicasCompras;
use App\Models\Pagamentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\MPApiException;
use App\Mail\MembershipWelcomeMail;
use App\Mail\MembershipPurchaseAdminMail;
use App\Models\User;

class PagamentosController extends Controller
{
    public function index_buy_attempts(Request $request)
    {
        Log::info('User accessed buy attempts page', ['user_id' => auth()->id()]);
        return view('buy.buy_attempts');
    }

    public function buy_attempts(Request $request)
    {
        $quantidade = $request->input('quantidade');
        $valor = $quantidade * env('PRICE_PER_ATTEMPT', 0.25);
        $desc = "Compra de {$quantidade} palpites ";

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

    public function webhook(Request $request)
    {
        $payload = $request->getContent();

        try {
            $event = json_decode($payload, true);

            if (!$event) {
                Log::error('MercadoPago webhook invalid JSON payload', [
                    'payload_length' => strlen($payload)
                ]);
                return response()->json(['error' => 'Invalid payload'], 400);
            }
        } catch (\Exception $e) {
            Log::error('MercadoPago webhook JSON decode failed', [
                'payload_length' => strlen($payload),
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('MercadoPago webhook received', [
            'event_type' => $event['type'] ?? 'unknown',
            'event_id' => $event['id'] ?? 'unknown'
        ]);

        if (($event['type'] ?? null) === 'payment') {
            $paymentData = $event['data'] ?? null;

            if (!$paymentData) {
                Log::error('MercadoPago webhook missing payment data');
                return response()->json(['error' => 'Missing payment data'], 400);
            }

            $paymentId = $paymentData['id'] ?? null;

            if (!$paymentId) {
                Log::error('MercadoPago webhook missing payment ID', [
                    'event_id' => $event['id'] ?? 'unknown'
                ]);
                return response()->json(['error' => 'Missing payment ID'], 400);
            }

            // Get payment details from MercadoPago
            try {
                MercadoPagoConfig::setAccessToken(env("MERCADO_PAGO_ACCESS_TOKEN"));
                $client = new PaymentClient();
                $payment = $client->get($paymentId);

                if ($payment->status === 'approved') {
                    // Process payment based on type
                    $pagamento = Pagamentos::where('payment_id', $paymentId)->first();
                    if ($pagamento && !$pagamento->processed) {
                        $user = $pagamento->user;

                        if (!$user) {
                            Log::error('User not found for MercadoPago webhook payment', [
                                'payment_id' => $paymentId,
                                'user_id' => $pagamento->user_id
                            ]);
                            return response()->json(['error' => 'User not found'], 400);
                        }

                        // Check payment type and process accordingly
                        if (str_contains($pagamento->desc, 'VIP mensal')) {
                            // Process VIP membership
                            $user->is_vip = true;
                            $user->membership_expires_at = now()->addMonth();
                            $user->save();

                            Log::info('User upgraded to VIP via MercadoPago webhook', [
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
                            Mail::to($user->email)->queue((new MembershipWelcomeMail($user))->track($user->email, 'Bem-vindo aos VIPs!'));

                            // Notify admins
                            $admins = User::where('is_admin', 'S')->get();
                            foreach ($admins as $admin) {
                                Mail::to($admin->email)->queue((new MembershipPurchaseAdminMail($user))->track($admin->email, 'Novo usuário adquiriu membership VIP!'));
                            }

                        } elseif (str_contains($pagamento->desc, 'palpites')) {
                            // Process attempts purchase
                            $quantidade = (int) filter_var($pagamento->desc, FILTER_SANITIZE_NUMBER_INT);
                            $indicated = AdicionaisIndicacao::where('user_uuid', $user->uuid)->first();
                            if (!empty($indicated)) {
                                $indicated->value = $indicated->value + $quantidade;
                                $indicated->save();
                            } else {
                                AdicionaisIndicacao::create(['user_uuid' => $user->uuid, 'value' => $quantidade]);
                            }
                            $uuid = $user->uuid;
                            Cache::delete("indicacoes_{$uuid}");

                        } elseif (str_contains($pagamento->desc, 'dica')) {
                            // Process dica purchase - extract adivinhacao_id from description
                            preg_match('/Compra de dica - (.+)/', $pagamento->desc, $matches);
                            if (isset($matches[1])) {
                                $titulo = $matches[1];
                                $adivinhacao = Adivinhacoes::where('titulo', $titulo)->first();
                                if ($adivinhacao) {
                                    DicasCompras::create([
                                        'user_id' => $user->id,
                                        'adivinhacao_id' => $adivinhacao->id,
                                        'pagamento_id' => $pagamento->id
                                    ]);
                                }
                            }
                        }

                        $pagamento->processed = true;
                        $pagamento->save();

                        Log::info('Payment processed successfully via MercadoPago webhook', [
                            'user_id' => $user->id,
                            'payment_id' => $paymentId,
                            'type' => $this->getPaymentType($pagamento->desc)
                        ]);
                    }
                } else {
                    Log::warning('MercadoPago webhook payment not approved', [
                        'payment_id' => $paymentId,
                        'status' => $payment->status ?? 'unknown'
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error processing MercadoPago webhook payment', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Error processing payment'], 500);
            }
        }

        return response()->json(['status' => 'success']);
    }

    private function getPaymentType($description)
    {
        if (str_contains($description, 'VIP mensal')) {
            return 'vip';
        } elseif (str_contains($description, 'palpites')) {
            return 'attempts';
        } elseif (str_contains($description, 'dica')) {
            return 'dica';
        }
        return 'unknown';
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
                // Process payment based on type
                $pagamento = Pagamentos::where('payment_id', $paymentId)->first();
                if ($pagamento && !$pagamento->processed) {
                    $user = $pagamento->user;

                    // Check if it's attempts purchase or dica purchase
                    if (str_contains($pagamento->desc, 'palpites')) {
                        // Process attempts purchase
                        $quantidade = (int) filter_var($pagamento->desc, FILTER_SANITIZE_NUMBER_INT);
                        $indicated = AdicionaisIndicacao::where('user_uuid', $user->uuid)->first();
                        if (!empty($indicated)) {
                            $indicated->value = $indicated->value + $quantidade;
                            $indicated->save();
                        } else {
                            AdicionaisIndicacao::create(['user_uuid' => $user->uuid, 'value' => $quantidade]);
                        }
                        $uuid = $user->uuid;
                        Cache::delete("indicacoes_{$uuid}");
                    } elseif (str_contains($pagamento->desc, 'dica')) {
                        // Process dica purchase - extract adivinhacao_id from description
                        preg_match('/Compra de dica - (.+)/', $pagamento->desc, $matches);
                        if (isset($matches[1])) {
                            $titulo = $matches[1];
                            $adivinhacao = Adivinhacoes::where('titulo', $titulo)->first();
                            if ($adivinhacao) {
                                DicasCompras::create([
                                    'user_id' => $user->id,
                                    'adivinhacao_id' => $adivinhacao->id,
                                    'pagamento_id' => $pagamento->id
                                ]);
                            }
                        }
                    }

                    $pagamento->processed = true;
                    $pagamento->save();

                    Log::info('Payment processed successfully', [
                        'user_id' => $user->id,
                        'payment_id' => $paymentId,
                        'type' => str_contains($pagamento->desc, 'palpites') ? 'attempts' : 'dica'
                    ]);
                }
            }

            return response()->json(['status' => $payment->status]);
        } catch (\Exception $e) {
            Log::error('Error checking payment status: ' . $e->getMessage());
            return response()->json(['status' => 'error']);
        }
    }

    public function index_buy_dica(Request $request, Adivinhacoes $adivinhacao)
    {
        Log::info('User accessed buy dica page', ['user_id' => auth()->id(), 'adivinhacao_id' => $adivinhacao->id]);
        if ($adivinhacao->dica_paga == 'S') {

            return view('buy.buy_dica')->with(compact('adivinhacao'));
        }
        return redirect()->route('home');
    }

    public function buy_attempts_pix(Request $request)
    {
        $quantidade = $request->input('quantidade');
        $valor = $quantidade * env('PRICE_PER_ATTEMPT', 0.25);
        $desc = "Compra de {$quantidade} palpites ";

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
                    'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
                    'payment_id' => $payment->id
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

    public function buy_dica(Request $request, Adivinhacoes $adivinhacao)
    {
        $desc = "Compra de dica - {$adivinhacao->titulo}";

        try {

            $pag =  Pagamentos::create([
                'user_id' => auth()->id(),
                'value' => $adivinhacao->dica_valor,
                'desc' => $desc,
            ]);
            MercadoPagoConfig::setAccessToken(env("MERCADO_PAGO_ACCESS_TOKEN"));

            $client = new PaymentClient();
            $request_options = new RequestOptions();
            $uniqueKey = now()->timestamp . $pag->id . md5(now()->timestamp);
            $request_options->setCustomHeaders(["X-Idempotency-Key: {$uniqueKey}"]);

            $payment = $client->create([
                "transaction_amount" => $adivinhacao->dica_valor,
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
        } catch (MPApiException $e) {
            Log::error("Status code: " . $e->getApiResponse()->getStatusCode() . "\n");
            Log::error($e->getApiResponse()->getContent());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
