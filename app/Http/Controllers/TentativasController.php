<?php

namespace App\Http\Controllers;

use App\Models\AdicionaisIndicacao;
use App\Models\Pagamentos;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\MercadoPagoConfig;

use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\MPApiException;

class TentativasController extends Controller
{
    public function index(Request $request)
    {
        return view('buy_attempts');
    }

    public function buy(Request $request)
    {

        $quantidade = $request->input('quantidade');
        $valor = $quantidade * 0.10;
        $desc = "Compra de {$quantidade} tentativas ";


        // try {

            $pag =  Pagamentos::create([
                'user_id' => auth()->id(),
                'value' => $valor,
                'desc' => $desc,
            ]);
            MercadoPagoConfig::setAccessToken("MERCADO_PAGO_ACCESS_TOKEN");

            $client = new PaymentClient();
            $request_options = new RequestOptions();
            $uniqueKey = now()->timestamp.$pag->id.md5(now()->timestamp);
            $request_options->setCustomHeaders(["X-Idempotency-Key: {$uniqueKey}"]);

            $payment = $client->create([
                "transaction_amount" => $request->input('transaction_amount'),
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
            Log::info('new mercado pago payment', $payment);
            $pag->payment_status = $payment->status;
            $pag->payment_id = $payment->id;
            $pag->save();

            $indicated = AdicionaisIndicacao::where('user_uuid',  auth()->user()->uuid)->first();
            if (!empty($indicated)) {
                $indicated->value = $indicated->value + $request->input('quantidade');
                $indicated->save();
            } else {
                AdicionaisIndicacao::create(['user_uuid' => auth()->user()->uuid, 'value' => $request->input('quantidade')]);
            }

            return response()->json(['success' => true]);
        // } catch (\Exception $e) {
        //     Log::error($e->getMessage());

        //     return response()->json(['success' => false], 400);
        // }
    }



    public function webhook(Request $request)
    {
        Log::info('Webhook mercado pago', $request->all());

        return response()->json(['message' => 'OK'], 200);
    }
}
