<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\DicasCompras;
use App\Models\Pagamentos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\MPApiException;

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
        Log::info('Webhook mercado pago', $request->all());
        return response()->json(['message' => 'OK'], 200);
    }

    public function checkPaymentStatus(Request $request)
    {
        $paymentId = $request->input('payment_id');

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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
