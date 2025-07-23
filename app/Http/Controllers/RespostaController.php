<?php

namespace App\Http\Controllers;

use App\Events\AumentaContagemRespostas;
use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RespostaController extends Controller
{
    public function enviar(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $userUuid = $user->uuid;
        $hoje = today()->toDateString();

        // Cache de tentativas
        $cacheTryKey = "try_count_user_{$userId}_{$hoje}";
        $countTrysToday = Cache::remember($cacheTryKey, 60, function () use ($userId) {
            return AdivinhacoesRespostas::where('user_id', $userId)->whereDate('created_at', today())->count();
        });

        // Cache de adicionais
        $cacheAdicionalKey = "indicacao_user_{$userUuid}";
        $countFromIndications = Cache::remember($cacheAdicionalKey, 300, function () use ($userUuid) {
            return AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
        });

        $limiteMax = env('MAX_ADIVINHATIONS', 10);
        if ($countTrysToday >= ($limiteMax + $countFromIndications)) {
            return response()->json(['error' => "Você atingiu seu limite respostas de hoje!"]);
        }

        $data = $request->validate([
            'resposta'       => 'required|string',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);

        $respostaCliente = strtolower(trim($data['resposta']));

        // Evita repetição da mesma resposta
        if (AdivinhacoesRespostas::where([
            ['adivinhacao_id', $data['adivinhacao_id']],
            ['user_id', $userId],
            ['resposta', $respostaCliente],
        ])->exists()) {
            return response()->json(['error' => "Você já tentou isso!"]);
        }

        try {
            DB::beginTransaction();

            $adivinhacao = Adivinhacoes::find($data['adivinhacao_id']);
            if (!$adivinhacao) {
                throw new \Exception("Adivinhação não encontrada.");
            }

            $respostaUuid = (string) Str::uuid();

            AdivinhacoesRespostas::insert([
                'user_id'        => $userId,
                'adivinhacao_id' => $adivinhacao->id,
                'resposta'       => $respostaCliente,
                'created_at'     => now(),
                'uuid'           => $respostaUuid
            ]);

            // Incrementa contagem de tentativas no cache
            Cache::increment($cacheTryKey);

            // Contador de respostas por adivinhação no cache
            Cache::increment("respostas_adivinhacao_{$adivinhacao->id}", 1);

            broadcast(new AumentaContagemRespostas($adivinhacao->id));

            // Se acabou as tentativas grátis e ainda há bônus
            if (($countTrysToday + 1) >= $limiteMax && $countFromIndications > 0) {
                $indicacao = AdicionaisIndicacao::where('user_uuid', $userUuid)->first();
                if ($indicacao) {
                    $indicacao->decrement('value');
                    Cache::decrement($cacheAdicionalKey);
                }
            }

            // Se acertou
            if (strtolower(trim($adivinhacao->resposta)) === $respostaCliente) {
                $adivinhacao->update(['resolvida' => 'S']);

                broadcast(new RespostaAprovada($user->username, $adivinhacao))->toOthers();

                broadcast(new RespostaPrivada(
                    "Você acertou! Seu código de resposta: {$respostaUuid}!!!\n Em breve será notificado do envio do prêmio.",
                    $adivinhacao->id,
                    $userId,
                    "Acertou"
                ));

                AdivinhacoesPremiacoes::create([
                    'user_id'        => $userId,
                    'adivinhacao_id' => $adivinhacao->id,
                ]);

                DB::commit();
                return response()->json(['status' => 'acertou']);
            }

            DB::commit();
            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Não foi possível inserir sua resposta agora, tente novamente mais tarde...']);
        }
    }
}
