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
    /**
     * Como são milhares de requisições por minuto, eu deixo as insereções no banco de dados por ultimo, primeiro eu verifico se o usuario pode responder, se puder, busco a adivinhação, verifico se ela pode ser respondida, se puder, verifico se ele acertou, se ele acertou, instantanemaneto bloqueio a adivinhação para ningume mais acertar e ai sigo os outros processos que não são tão importantes
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

        #verifica se o cliente ja tentou pra economizar uma consulta na adivinhacao
        if (AdivinhacoesRespostas::where([
            ['adivinhacao_id', $data['adivinhacao_id']],
            ['user_id', $userId],
            ['resposta', $respostaCliente],
        ])->exists()) {
            return response()->json(['error' => "Você já tentou isso!"]);
        }

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

        //aqui algumas coisas que não tão importantes pra ser rapido
        try {

            DB::beginTransaction();

            //usado insert para melhorar tempo de resposta do banco
            AdivinhacoesRespostas::insert([
                'user_id'        => $userId,
                'adivinhacao_id' => $data['adivinhacao_id'],
                'resposta'       => $respostaCliente,
                'created_at'     => now(),
                'uuid'           => $respostaUuid
            ]);

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


            //aumenta o contador de respotas 
            $respostaCacheKey = "respostas_adivinhacao_{$data['adivinhacao_id']}";
            $countRespostas = Cache::get($respostaCacheKey, 0) + 1;
            Cache::put($respostaCacheKey, $countRespostas);
            broadcast(new AumentaContagemRespostas($data['adivinhacao_id'], $countRespostas));

            
            DB::commit();

            //aqui adiciona o premio para o ganhador e faze retorno do http
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
