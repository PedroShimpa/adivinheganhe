<?php

namespace App\Http\Controllers;

use App\Events\RespostaAprovada;
use App\Events\RespostaPrivada;
use App\Events\NewResponseAdded;
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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RespostaController extends Controller
{
    public function enviar(Request $request)
    {
        if (!$this->respostasHabilitadas()) return;

        $adivinhacaoId = $request->input('adivinhacao_id');

        if ($this->adivinhacaoJaResolvida($adivinhacaoId) || $this->adivinhacaoBloqueada($adivinhacaoId)) {
            return response()->json(['info' => "Esta adivinhação já foi adivinhada, obrigado por tentar!"]);
        }

        $data = $this->validarRequisicao($request);
        $user = Auth::user();
        $userId = $user->id;
        $userUuid = $user->uuid;

        if ($this->ultrapassouLimite($userId, $userUuid, $adivinhacaoId)) {
            return response()->json(['info' => "Você já ultilizou todos os seus palpites!"]);
        }

        $respostaCliente = mb_strtolower(trim($data['resposta']));
        $adivinhacao = $this->buscarAdivinhacao($data['adivinhacao_id']);

        // Check if adivinhacao is VIP-only or released to VIPs first and user is not VIP
        if (($adivinhacao->only_members == 1 || (!is_null($adivinhacao->vip_release_at) && now()->lt($adivinhacao->vip_release_at))) && !auth()->user()->isVip()) {
             return response()->json(['info' => "Esta adivinhação é apenas para membrs vips, adiquira o seu em: https://adivinheganhe.com.br/seja-membro!"]);
        }


        if ($this->naoPodeResponder($adivinhacao)) {
            return response()->json(['info' => "Esta adivinhação já foi adivinhada ou expirou. Obrigado por tentar!"]);
        }

        $respostaUuid = (string) Str::uuid();
        $acertou = mb_strtolower(trim($adivinhacao->resposta)) === $respostaCliente;

        if ($this->respostaJaEnviada($data['adivinhacao_id'], $userId, $respostaCliente)) {
            return response()->json(['info' => 'Você já tentou isso!'], 409);
        }

        AdivinhacoesRespostas::insertOrIgnore([
            'uuid' => $respostaUuid,
            'adivinhacao_id' => $data['adivinhacao_id'],
            'user_id' => $userId,
            'resposta' => $respostaCliente,
            'created_at' => now()
        ]);

        if ($acertou) {
            $this->processarAcerto($adivinhacao, $user, $respostaUuid, $respostaCliente);
        }

        // Dispatch event for real-time dashboard update
        try {
            $totalResponses = AdivinhacoesRespostas::count();
            broadcast(new NewResponseAdded($totalResponses));
        } catch (\Exception $e) {
            \Log::error('Failed to dispatch NewResponseAdded event: ' . $e->getMessage());
            // Continue without failing the response submission
        }

        return $this->responderAoUsuario($acertou, $user, $adivinhacao, $respostaUuid, $userUuid);
    }

    private function respostasHabilitadas()
    {
        return env('ENABLE_REPLY', true);
    }

    private function adivinhacaoJaResolvida($id)
    {
        return Cache::get('adivinhacao_resolvida' . $id);
    }

    private function adivinhacaoBloqueada($id)
    {
        return Cache::get('adivinhacao_bloqueada_' . $id);
    }

    private function validarRequisicao(Request $request)
    {
        return $request->validate([
            'resposta' => 'required|string|max:255',
            'adivinhacao_id' => 'required|exists:adivinhacoes,id',
        ]);
    }

    private function ultrapassouLimite(int $userId, string $userUuid, int $adivinhacaoId)
    {
        $count = AdivinhacoesRespostas::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->where('adivinhacao_id', $adivinhacaoId)
            ->count();
        $limite = auth()->user()->isVip() ? 7 : env('MAX_ADIVINHATIONS', 10);
        $bonus = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;

        return $count >= $limite && $bonus == 0;
    }

    private function buscarAdivinhacao($id)
    {
        return Adivinhacoes::find($id);
    }

    private function naoPodeResponder($adivinhacao)
    {

        return $adivinhacao->resolvida == 'S' || (!empty($adivinhacao->expire_at) && $adivinhacao->expire_at < now());
    }

    private function processarAcerto($adivinhacao, $user, $respostaUuid, $respostaCliente)
    {
        $cacheKey = 'adivinhacao_' . $adivinhacao->id;

        Cache::set('adivinhacao_resolvida' . $adivinhacao->id, true);
        $adivinhacao->update(['resolvida' => 'S']);
        $adivinhacao->resolvida = 'S';
        Cache::put($cacheKey, $adivinhacao, now()->addMinutes(10));

        // Block all input fields for this adivinhacao
        Cache::set('adivinhacao_bloqueada_' . $adivinhacao->id, true, now()->addMinutes(10));

        broadcast(new RespostaAprovada($user->username, $adivinhacao))->toOthers();

        broadcast(new RespostaPrivada(
            "Você acertou! Seu código de resposta: {$respostaUuid}!!!\n Em breve será notificado do envio do prêmio.",
            $adivinhacao->id,
            $user->id,
            "Acertou"
        ));

        // Send WhatsApp message for correct guess
        try {
            $API_BASE = env('NOTIFICACAO_API_BASE');
            $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
            $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
            $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

            $tokenRes = Http::post($TOKEN_ENDPOINT);

            if ($tokenRes->successful() && $tokenRes->json('status') === 'success') {
                $token = $tokenRes->json('token');
                $headers = ["Authorization" => "Bearer $token"];

                $username = $user->isVip() ? "VIP {$user->username}" : $user->username;
                $mensagem = "O jogador {$username} acertou a adivinhacao {$adivinhacao->titulo}, a resposta correta era: {$respostaCliente}, parabens! Em breve nossa equipe entrará em contato para pagamento do prêmio\nVejam todas as respostas enviadas em https://adivinheganhe.com.br/adivinhacoes/{$adivinhacao->uuid}";

                $payload = [
                    "phone" => $PHONE_ID,
                    "isGroup" => false,
                    "isNewsletter" => true,
                    "isLid" => false,
                    "message" => $mensagem,
                ];

                $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);

                if (!$resp->successful()) {
                    Log::error("Erro ao enviar mensagem WhatsApp para acerto: " . $resp->body());
                }
            } else {
                Log::error("Erro ao gerar token para WhatsApp acerto: " . $tokenRes->body());
            }
        } catch (\Exception $e) {
            Log::error("Erro ao enviar notificação WhatsApp para acerto: " . $e->getMessage());
        }
    }

    private function respostaJaEnviada($adivinhacaoId, $userId, $resposta)
    {
        return AdivinhacoesRespostas::where([
            'adivinhacao_id' => $adivinhacaoId,
            'user_id' => $userId,
            'resposta' => $resposta,
        ])->exists();
    }

    private function responderAoUsuario($acertou, $user, $adivinhacao, $respostaUuid, $userUuid)
    {
        try {
            $count = AdivinhacoesRespostas::where('user_id', $user->id)
                ->where('adivinhacao_id', $adivinhacao->id)
                ->whereDate('created_at', today())
                ->count();

            $bonus = AdicionaisIndicacao::where('user_uuid', $userUuid)->value('value') ?? 0;
            $limite = (auth()->user()->isVip() ? 7 : env('MAX_ADIVINHATIONS', 10)) + $bonus;

            if (($count >= (auth()->user()->isVip() ? 7 : env('MAX_ADIVINHATIONS', 10))) && $bonus > 0) {
                $indicacao = AdicionaisIndicacao::where('user_uuid', $userUuid)->first();
                $indicacao?->decrement('value');
                $trysRestantes = $indicacao->value ?? 0;
            } else {
                $trysRestantes = $limite - $count;
            }

            Cache::forget('resposta_do_usuario_hoje_' . auth()->user()->id);
            if ($acertou) {

                AdivinhacoesPremiacoes::create([
                    'user_id' => $user->id,
                    'adivinhacao_id' => $adivinhacao->id,
                ]);

                Mail::to($user->email)->queue((new AcertoUsuarioMail($user->name, $adivinhacao))->track($user->email, 'Parabéns! Você acertou a adivinhação!'));
                $admins = User::where('is_admin', 'S')->get();

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->queue((new AcertoAdminMail($user, $adivinhacao))->track($admin->email, 'Um usuário acertou uma adivinhação!'));
                }

                return response()->json([
                    'message' => 'acertou',
                    'reply_code' => $respostaUuid,
                    'trys' => $trysRestantes,
                ]);
            }

            return response()->json([
                'message' => 'ok',
                'reply_code' => $respostaUuid,
                'trys' => $trysRestantes,
            ]);
        } catch (QueryException $e) {
            Log::error('Erro ao adicionar premiação ' . $e->getMessage());
            return response()->json(['error' => 'Não foi possível inserir sua resposta agora, tente novamente mais tarde...']);
        }
    }
}
