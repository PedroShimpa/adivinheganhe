<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoNovaAdivinhacao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificarEmail extends Command
{
    protected $signature = 'notificar:email {adivinhacao_id}';
    protected $description = 'Notifica uma adivinhação específica via Email';

    public function handle()
    {
        $adivinhacaoId = $this->argument('adivinhacao_id');

        $adiv = DB::table('adivinhacoes')->where('id', $adivinhacaoId)->first();

        if (!$adiv) {
            $this->error("Adivinhação não encontrada.");
            return Command::FAILURE;
        }

        try {
            $titulo = $adiv->titulo;
            $url = route('adivinhacoes.index', $adiv->uuid);
            dispatch(new EnviarNotificacaoNovaAdivinhacao($titulo, $url, $adiv->id));
            DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_email_em' => now()]);
            $this->info("Notificação Email enviada para adivinhação {$adiv->titulo}");
        } catch (\Exception $e) {
            $this->error("Erro ao enviar notificação Email: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
