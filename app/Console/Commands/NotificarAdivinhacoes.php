<?php

namespace App\Console\Commands;

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
            ->select('id', 'titulo', 'uuid')
            ->whereNotNull('liberado_at')
            ->where('liberado_at', '<', now())
            ->where('notificado_canal_whatsapp', 0)
            ->get();

        if ($adivinhacoes->isEmpty()) {
            return Command::SUCCESS;
        }

        $API_BASE = env('NOTIFICACAO_API_BASE');
        $TOKEN_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_TOKEN_PATH');
        $SEND_MESSAGE_ENDPOINT = $API_BASE . env('NOTIFICACAO_API_SEND_PATH');
        $PHONE_ID = env('NOTIFICACAO_PHONE_ID');

        // Obter token
        $tokenRes = Http::post($TOKEN_ENDPOINT);

        if (!$tokenRes->successful() || $tokenRes->json('status') !== 'success') {
            $this->error("Erro ao gerar token: " . $tokenRes->body());
            Log::error("Erro ao gerar token: " . $tokenRes->body());
            return Command::FAILURE;
        }

        $token = $tokenRes->json('token');
        $headers = ["Authorization" => "Bearer $token"];

        foreach ($adivinhacoes as $adiv) {
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
                    DB::table('adivinhacoes')
                        ->where('id', $adiv->id)
                        ->update(['notificado_canal_whatsapp' => 1]);
                } else {
                    $msg = "❌ Falha ao enviar mensagem para {$adiv->titulo}: " . $resp->body();
                    $this->error($msg);
                    Log::error($msg);
                }
            } catch (\Exception $e) {
                $msg = "❌ Exceção ao enviar mensagem para {$adiv->titulo}: {$e->getMessage()}";
                $this->error($msg);
                Log::error($msg);
            }
        }
    
        return Command::SUCCESS;
    }
}
