<?php

namespace App\Http\Controllers;

use App\Events\AumentaContagemRespostas;
use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Models\AdicionaisIndicacao;
use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RespostaController extends Controller
{
    /**
     * Como são milhares de requisições por minuto, eu deixo as insereções no banco de dados por ultimo, primeiro eu verifico se o usuario pode responder, se puder, busco a adivinhação, verifico se ela pode ser respondida, se puder, verifico se ele acertou, se ele acertou, instantanemaneto bloqueio a adivinhação para ningume mais acertar e ai sigo os outros processos que não são tão importantes
     * 
     *A tabela de respostas vai ser gigante, então fazemos o minimo de consultas possivel nela pra evitar gargalo no banco
     *A tebala de respostas tem que estar sempre muito bem indexada
     */

    public function enviar(Request $request)
    {
        $data = $request->validate([
            'resposta'       => 'required|string',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);

        #verifica se ja foi adivinhada, se ja foi, ja para aqui
        if (Cache::get('adivinhaca_resolvida' . $data['adivinhacao_id'])) {
            return response()->json(['error' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
        }

        $user = Auth::user();
        $userId = $user->id;
        $userUuid = $user->uuid;

        #verifica se o usuario tem tentativas ainda
        $hoje = today()->toDateString();
        $cacheTryKey = "try_count_user_{$userId}_{$hoje}";
        $countTrysToday = Cache::get($cacheTryKey);
        $limiteMax = env('MAX_ADIVINHATIONS', 10);
        $cacheAdicionalKey = "indicacao_user_{$userUuid}";
        $countFromIndications = Cache::get($cacheAdicionalKey);

        if (is_null($countTrysToday)) {
            $countTrysToday = AdivinhacoesRespostas::where('user_id', $userId)->whereDate('created_at', today())->count();
            Cache::put($cacheTryKey, $countTrysToday, now()->addSeconds(60));
        }

        if (is_null($countFromIndications)) {
            $countFromIndications = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            Cache::put($cacheAdicionalKey, $countFromIndications, now()->addSeconds(300));
        }

        if ($countTrysToday >= ($limiteMax + $countFromIndications)) {
            return response()->json(['error' => "Você já ultilizou todas as suas tentativas!"]);
        }

        $respostaCliente = mb_strtolower(trim($data['resposta']));

        #busca a adivinhacao e verifica novsmente se ela ja foi resolvida ou expirou
        $adivinhacao = Adivinhacoes::find($data['adivinhacao_id']);
        if ($adivinhacao->resolvida == 'S') {
            return response()->json(['error' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
        }
        if (!empty($adivinhacao->expire_at) && $adivinhacao->expire_at < now()) {
            return response()->json(['error' => "Esta adivinhação expirou! Obrigado por tentar!"]);
        }

        $respostaUuid = (string) Str::uuid();

        $acertou = mb_strtolower(trim($adivinhacao->resposta)) === $respostaCliente;

        //se acertou, a primeira coisa que deve fazer  é salvar para ninguem mais acertar e bloquear a proxima requisição
        if ($acertou) {
            Cache::set('adivinhacao_resolvida' . $data['adivinhacao_id'], true);
            $adivinhacao->update(['resolvida' => 'S']);

            //avisa todos os outros que a adivinhação encerrou e bloqueia o campo
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

            return response()->json(['ok' => true], 201);
        } catch (QueryException $e) {
            // 23000 = integrity constraint violation | 1062 = duplicate entry (MySQL/MariaDB)
            if ($e->getCode() === '23000' || ($e->errorInfo[1] ?? null) === 1062) {
                return response()->json(['error' => 'Você já tentou isso!'], 409);
            }

            // outras falhas
            report($e);
            return response()->json(['error' => 'Erro inesperado'], 500);
        }


        //aqui algumas coisas que não tão importantes pra ser rapido
        try {


            //aumenta as tentativas diarias do usuario
            $countTrysToday++;
            Cache::put($cacheTryKey, $countTrysToday);

            //se o usuario ja usou todas as respostas do dia, deve verificar se ele tem bonus e remover
            if (($countTrysToday >= $limiteMax) && $countFromIndications > 0) {
                $indicacao = AdicionaisIndicacao::where('user_uuid', $userUuid)->first();
                if ($indicacao) {
                    $indicacao->decrement('value');
                    $countFromIndications = max(0, $countFromIndications - 1);
                    Cache::put($cacheAdicionalKey, $countFromIndications);
                }
            }

            if ($acertou) {
                Cache::delete('adivinhacoes_ativas');
                Cache::delete('premios_ultimos');

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
            return response()->json(['error' => 'Não foi possível inserir sua resposta agora, tente novamente mais tarde...']);
        }
    }
}
