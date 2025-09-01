<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoNovaAdivinhacao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificarAdivinhacoes extends Command
{
    protected $signature = 'notificar:adivinhacoes';
    protected $description = 'Notifica novas adivinhações no canal WhatsApp';

    public function handle()
    {
        $adivinhacoes = DB::table('adivinhacoes')
            ->whereNotNull('liberado_at')
            ->where('liberado_at', '<', now())
            ->where(
                function ($q) {
                    $q->where('notificar_email', 1);
                    $q->orWhere('notificar_whatsapp', 1);
                }
            )
            ->whereNull('notificado_email_em')
            ->whereNull('notificado_whatsapp_em')
            ->get();

        if ($adivinhacoes->isEmpty()) {
            return Command::SUCCESS;
        }

        $API_BASE = env('NOTIFICACAO_API_BASE');
        $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
        $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
        $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

        $tokenRes = Http::post($TOKEN_ENDPOINT);

        if (!$tokenRes->successful() || $tokenRes->json('status') !== 'success') {
            $this->error("Erro ao gerar token: " . $tokenRes->body());
            Log::error("Erro ao gerar token: " . $tokenRes->body());
            return Command::FAILURE;
        }

        $token = $tokenRes->json('token');
        $headers = ["Authorization" => "Bearer $token"];

        foreach ($adivinhacoes as $adiv) {
            if ($adiv->notificar_email == 1) {

                $titulo = $adiv->titulo;
                $url = route('adivinhacoes.index', $adiv->uuid);
                dispatch(new EnviarNotificacaoNovaAdivinhacao($titulo, $url));

                DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_email_em' => now()]);
            }
            if ($adiv->notificar_whatsapp == 1) {
                $mensagem = "*Nova adivinhação adicionada!*\n{$adiv->titulo}\nJogue em: https://adivinheganhe.com.br/adivinhacoes/{$adiv->uuid}";

                $payload = [
                    "phone" => $PHONE_ID,
                    "isGroup" => false,
                    "isNewsletter" => true,
                    "isLid" => false,
                    "message" => $mensagem,
                ];

                try {
                    $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);
                    if ($resp->successful()) {
                        DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_whatsapp_em' => now()]);
                    } else {
                        $msg = "Falha ao enviar mensagem para {$adiv->titulo}: " . $resp->body();
                        $this->error($msg);
                        Log::error($msg);
                    }
                } catch (\Exception $e) {
                    $msg = "Exceção ao enviar mensagem para {$adiv->titulo}: {$e->getMessage()}";
                    $this->error($msg);
                    Log::error($msg);
                }
            }
        }

        return Command::SUCCESS;
    }
}
