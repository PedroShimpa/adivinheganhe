<?php

namespace App\Http\Controllers;

use App\Events\AumentaContagemRespostas;
use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
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

        $cacheTryKey = "try_count_user_{$userId}_{$hoje}";
        $countTrysToday = Cache::get($cacheTryKey);
        if (is_null($countTrysToday)) {
            $countTrysToday = AdivinhacoesRespostas::where('user_id', $userId)->whereDate('created_at', today())->count();
            Cache::put($cacheTryKey, $countTrysToday, now()->addSeconds(60));
        }

        $cacheAdicionalKey = "indicacao_user_{$userUuid}";
        $countFromIndications = Cache::get($cacheAdicionalKey);
        if (is_null($countFromIndications)) {
            $countFromIndications = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            Cache::put($cacheAdicionalKey, $countFromIndications, now()->addSeconds(300));
        }

        $limiteMax = env('MAX_ADIVINHATIONS', 10);
        if ($countTrysToday >= ($limiteMax + $countFromIndications)) {
            return response()->json(['error' => "Você atingiu seu limite respostas de hoje!"]);
        }

        $data = $request->validate([
            'resposta'       => 'required|string',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);

        $respostaCliente = mb_strtolower(trim($data['resposta']));

        if (AdivinhacoesRespostas::where([
            ['adivinhacao_id', $data['adivinhacao_id']],
            ['user_id', $userId],
            ['resposta', $respostaCliente],
        ])->exists()) {
            return response()->json(['error' => "Você já tentou isso!"]);
        }

        try {
            $adivinhacao = Adivinhacoes::find($data['adivinhacao_id']);
            if ($adivinhacao->resolvida == 'S') {
                return response()->json(['error' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
            }
            if (!empty($adivinhacao->expire_at) && $adivinhacao->expire_at < now()) {
                return response()->json(['error' => "Esta adivinhação expirou! Em breve outra com o mesmo prêmio será adicionada!"]);
            }

            $respostaUuid = (string) Str::uuid();

            $acertou = mb_strtolower(trim($adivinhacao->resposta)) === $respostaCliente;

            if ($acertou) {
                $adivinhacao->update(['resolvida' => 'S']);
                broadcast(new RespostaAprovada($user->username, $adivinhacao))->toOthers();

                broadcast(new RespostaPrivada(
                    "Você acertou! Seu código de resposta: {$respostaUuid}!!!\n Em breve será notificado do envio do prêmio.",
                    $adivinhacao->id,
                    $userId,
                    "Acertou"
                ));
            }

            DB::beginTransaction();

            AdivinhacoesRespostas::insert([
                'user_id'        => $userId,
                'adivinhacao_id' => $data['adivinhacao_id'],
                'resposta'       => $respostaCliente,
                'created_at'     => now(),
                'uuid'           => $respostaUuid
            ]);

            $countTrysToday++;
            Cache::put($cacheTryKey, $countTrysToday, now()->addSeconds(30));

            $respostaCacheKey = "respostas_adivinhacao_{$data['adivinhacao_id']}";
            $countRespostas = Cache::get($respostaCacheKey, 0) + 1;
            Cache::put($respostaCacheKey, $countRespostas, now()->addSeconds(20));

            broadcast(new AumentaContagemRespostas($data['adivinhacao_id'], $countRespostas));

            if (($countTrysToday >= $limiteMax) && $countFromIndications > 0) {
                $indicacao = AdicionaisIndicacao::where('user_uuid', $userUuid)->first();
                if ($indicacao) {
                    $indicacao->decrement('value');
                    $countFromIndications = max(0, $countFromIndications - 1);
                    Cache::put($cacheAdicionalKey, $countFromIndications,  now()->addSeconds(60));
                }
            }

            DB::commit();

            if ($acertou) {
                Cache::delete('adivinhacoes_ativas');

                AdivinhacoesPremiacoes::create([
                    'user_id'        => $userId,
                    'adivinhacao_id' => $data['adivinhacao_id'],
                ]);
                Log::info("Premio adicionado para o usuario $userId");
                return response()->json(['status' => 'acertou', 'code' => $respostaUuid]);
            }

            return response()->json(['status' => 'ok', 'code' => $respostaUuid]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return response()->json(['error' => 'Não foi possível inserir sua resposta agora, tente novamente mais tarde...']);
        }
    }
}
