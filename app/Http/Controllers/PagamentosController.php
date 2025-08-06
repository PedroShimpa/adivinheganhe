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
                "token" => $request->input('token'),
                "description" => $desc,
                "installments" => 1,
                "payment_method_id" =>  $request->input('payment_method_id'),
                "issuer_id" => $request->input('issuer_id'),
                "payer" => [
                    "email" => $request->input('payer')['email'],
                    "identification" => [
                        "type" => $request->input('payer')['identification']['type'],
                        "number" => $request->input('payer')['identification']['number'],
                    ]
                ]
            ], $request_options);

            $pag->payment_status = $payment->status;
            $pag->payment_id = $payment->id;
            $pag->save();

            if ($payment->status == 'rejected') {
                return response()->json(['success' => false]);
            }
            $indicated = AdicionaisIndicacao::where('user_uuid',  auth()->user()->uuid)->first();
            if (!empty($indicated)) {
                $indicated->value = $indicated->value + $request->input('quantidade');
                $indicated->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => auth()->user()->uuid, 'value' => $request->input('quantidade')]);
            }

            $uuid = auth()->user()->uuid;
            Cache::delete("indicacoes_{$uuid}");
            return response()->json(['success' => true]);
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

    public function index_buy_dica(Request $request, Adivinhacoes $adivinhacao)
    {
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
                "token" => $request->input('token'),
                "description" => $desc,
                "installments" => 1,
                "payment_method_id" =>  $request->input('payment_method_id'),
                "issuer_id" => $request->input('issuer_id'),
                "payer" => [
                    "email" => $request->input('payer')['email'],
                    "identification" => [
                        "type" => $request->input('payer')['identification']['type'],
                        "number" => $request->input('payer')['identification']['number'],
                    ]
                ]
            ], $request_options);

            $pag->payment_status = $payment->status;
            $pag->payment_id = $payment->id;
            $pag->save();

            if ($payment->status == 'rejected') {
                return response()->json(['success' => false]);
            }

            DicasCompras::create(['user_id' => auth()->id, 'adivinhacao_id' => $adivinhacao->id, 'pagamento_id' => $pag->id]);

            return response()->json(['success' => true]);
        } catch (MPApiException $e) {
            Log::error("Status code: " . $e->getApiResponse()->getStatusCode() . "\n");
            Log::error($e->getApiResponse()->getContent());
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
