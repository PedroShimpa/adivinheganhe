<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TestFirebaseNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotificarPush extends Command
{
    protected $signature = 'notificar:push {adivinhacao_id}';
    protected $description = 'Notifica uma adivinhação específica via Push Notification';

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
            $users = User::whereNotNull('token_push_notification')->get();
            foreach ($users as $user) {
                $user->notify(new TestFirebaseNotification(
                    $titulo,
                    "Nova adivinhação disponível: {$titulo}",
                    ['url' => $url]
                ));
                DB::table('notificacoes')->insert([
                    'user_id' => $user->id,
                    'adivinhacao_id' => $adiv->id,
                    'tipo' => 'push',
                    'sent_at' => now(),
                ]);
            }
            DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_push_em' => now()]);
            $this->info("Notificação Push enviada para adivinhação {$adiv->titulo}");
        } catch (\Exception $e) {
            $this->error("Erro ao enviar notificação Push: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
