<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificarWhatsapp extends Command
{
    protected $signature = 'notificar:whatsapp {adivinhacao_id}';
    protected $description = 'Notifica uma adivinhação específica via WhatsApp';

    public function handle()
    {
        $adivinhacaoId = $this->argument('adivinhacao_id');

        $adiv = DB::table('adivinhacoes')->where('id', $adivinhacaoId)->first();

        if (!$adiv) {
            $this->error("Adivinhação não encontrada.");
            return Command::FAILURE;
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

        try {
            $mensagem = "*Nova adivinhação adicionada!*\n{$adiv->titulo}\nJogue em: https://adivinheganhe.com.br/adivinhacoes/{$adiv->uuid}";
            $payload = [
                "phone" => $PHONE_ID,
                "isGroup" => false,
                "isNewsletter" => true,
                "isLid" => false,
                "message" => $mensagem,
            ];
            $resp = Http::withHeaders($headers)->post($SEND_MESSAGE_ENDPOINT, $payload);
            if ($resp->successful()) {
                DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_whatsapp_em' => now()]);
                $this->info("Notificação WhatsApp enviada para adivinhação {$adiv->titulo}");
            } else {
                $this->error("Falha ao enviar notificação WhatsApp.");
            }
        } catch (\Exception $e) {
            $this->error("Erro ao enviar notificação WhatsApp: " . $e->getMessage());
            Log::error("Erro ao enviar notificação WhatsApp: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
