<?php

namespace App\Http\Controllers;

use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Mail\AcertoAdminMail;
use App\Mail\AcertoUsuarioMail;
use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RespostaController extends Controller
{
    /**
     * Como são milhares de requisições por minuto, eu deixo as insereções no banco de dados por ultimo, primeiro eu verifico se o usuario pode responder, se puder, busco a adivinhação, verifico se ela pode ser respondida, se puder, verifico se ele acertou, se ele acertou, instantanemaneto bloqueio a adivinhação para ningume mais acertar e ai sigo os outros processos que não são tão importantes
     * 
     * A tabela de respostas vai ser gigante, então fazemos o minimo de consultas possivel nela pra evitar gargalo no banco
     * A tebala de respostas tem que estar sempre muito bem indexada
     */

    public function enviar(Request $request)
    {
        if(!env('ENABLE_REPLY', true)) {
            return;
        }
        
        if (Cache::get('adivinhacao_resolvida' . $request->input('adivinhacao_id'))) {
            return response()->json(['info' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
        }


        $data = $request->validate([
            'resposta'       => 'required|string|max:255',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);

        $user = Auth::user();
        $userId = $user->id;
        $userUuid = $user->uuid;

        $hoje = today()->toDateString();
        $cacheTryKey = "try_count_user_{$userId}_{$hoje}";
        $countTrysToday = Cache::get($cacheTryKey);
        $limiteMax = env('MAX_ADIVINHATIONS', 10);
        $cacheAdicionalKey = "indicacao_user_{$userUuid}";
        $countFromIndications = Cache::get($cacheAdicionalKey);

        if (is_null($countTrysToday)) {
            $countTrysToday = AdivinhacoesRespostas::where('user_id', $userId)
                ->whereDate('created_at', today())
                ->count();
            Cache::put($cacheTryKey, $countTrysToday, now()->addSeconds(60));
        }

        if (is_null($countFromIndications)) {
            $countFromIndications = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            Cache::put($cacheAdicionalKey, $countFromIndications, now()->addSeconds(300));
        }

        if ($countTrysToday >= ($limiteMax + $countFromIndications)) {
            return response()->json(['info' => "Você já ultilizou todas as suas tentativas!"]);
        }

        $respostaCliente = mb_strtolower(trim($data['resposta']));

        $cacheKey = 'adivinhacao_' . $data['adivinhacao_id'];
        $adivinhacao = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($data) {
            return Adivinhacoes::find($data['adivinhacao_id']);
        });

        if ($adivinhacao->resolvida == 'S') {
            return response()->json(['info' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
        }
        if (!empty($adivinhacao->expire_at) && $adivinhacao->expire_at < now()) {
            return response()->json(['info' => "Esta adivinhação expirou! Obrigado por tentar!"]);
        }

        $respostaUuid = (string) Str::uuid();

        $acertou = mb_strtolower(trim($adivinhacao->resposta)) === $respostaCliente;

        if ($acertou) {
            Cache::set('adivinhacao_resolvida' . $data['adivinhacao_id'], true);

            $adivinhacao->update(['resolvida' => 'S']);

            $adivinhacao->resolvida = 'S';
            Cache::put($cacheKey, $adivinhacao, now()->addMinutes(10));

            broadcast(new RespostaAprovada($user->username, $adivinhacao))->toOthers();

            broadcast(new RespostaPrivada(
                "Você acertou! Seu código de resposta: {$respostaUuid}!!!\n Em breve será notificado do envio do prêmio.",
                $adivinhacao->id,
                $userId,
                "Acertou"
            ));
        }

        try {
            AdivinhacoesRespostas::insert([
                'uuid' => Str::uuid(),
                'adivinhacao_id' => $data['adivinhacao_id'],
                'user_id'        => $userId,
                'resposta'       => $respostaCliente,
                'created_at'     => now()
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['info' => 'Você já tentou isso!'], 409);
            }
            Log::error('Erro na adição de resposta:' . $e->getMessage());
            return response()->json(['error' => 'Erro inesperado'], 500);
        }

        $countTrysToday++;
        Cache::put($cacheTryKey, $countTrysToday);

        if (($countTrysToday >= $limiteMax) && $countFromIndications > 0) {
            $indicacao = AdicionaisIndicacao::where('user_uuid', $userUuid)->first();
            if ($indicacao) {
                $indicacao->decrement('value');
                $countFromIndications = max(0, $countFromIndications - 1);
                Cache::put($cacheAdicionalKey, $countFromIndications);
            }
        }

        try {
            if ($acertou) {
                Cache::forget('adivinhacoes_ativas');
                Cache::forget('premios_ultimos');

                AdivinhacoesPremiacoes::create([
                    'user_id'        => $userId,
                    'adivinhacao_id' => $data['adivinhacao_id'],
                ]);
                Log::info("Premio adicionado para o usuario $userId");

                Mail::to($user->email)->queue(new AcertoUsuarioMail($user->name, $adivinhacao));

                $admins = User::where('is_admin', 'S')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue(new AcertoAdminMail($user, $adivinhacao));
                }
                return response()->json(['message' => 'acertou', 'responde_code' => $respostaUuid], 200);
            }

            return response()->json(['message' => 'ok', 'responde_code' => $respostaUuid], 200);
        } catch (QueryException $e) {
            Log::error('Erro ao adicionar premiação ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível inserir sua resposta agora, tente novamente mais tarde...']);
        }
    }
}
