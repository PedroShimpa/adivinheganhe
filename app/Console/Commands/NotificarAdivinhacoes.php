<?php

namespace App\Console\Commands;

use App\Jobs\EnviarNotificacaoNovaAdivinhacao;
use App\Models\User;
use App\Notifications\TestFirebaseNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class NotificarAdivinhacoes extends Command
{
    protected $signature = 'notificar:adivinhacoes';
    protected $description = 'Notifica novas adivinhações via push notification';

    public function handle()
    {
        $adivinhacoes = DB::table('adivinhacoes')
            ->whereNotNull('liberado_at')
            ->where('liberado_at', '<', now())
            ->where(
                function ($q) {
                    $q->where('notificar_email', 1);
                    $q->orWhere('notificar_push', 1);
                }
            )
            ->whereNull('notificado_email_em')
            ->whereNull('notificado_push_em')
            ->get();

        if ($adivinhacoes->isEmpty()) {
            return Command::SUCCESS;
        }



        foreach ($adivinhacoes as $adiv) {
            if ($adiv->notificar_email == 1 && empty($adiv->notificado_email_em)) {

                $titulo = $adiv->titulo;
                $url = route('adivinhacoes.index', $adiv->uuid);
                dispatch(new EnviarNotificacaoNovaAdivinhacao($titulo, $url));

                DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_email_em' => now()]);
            }


            // Send push notification to users with tokens
            if ($adiv->notificar_push == 1 && empty($adiv->notificado_push_em)) {
                $titulo = $adiv->titulo;
                $url = route('adivinhacoes.index', $adiv->uuid);

                $users = User::whereNotNull('token_push_notification')->get();

                foreach ($users as $user) {
                    $user->notify(new TestFirebaseNotification(
                        $titulo,
                        "Nova adivinhação disponível: {$titulo}",
                        ['url' => $url]
                    ));
                }

                DB::table('adivinhacoes')->where('id', $adiv->id)->update(['notificado_push_em' => now()]);
            }
        }

        return Command::SUCCESS;
    }
}
