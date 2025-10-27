<?php

namespace App\Jobs;

use App\Mail\NotifyNewAdivinhacaoMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnviarNotificacaoNovaAdivinhacao implements ShouldQueue
{
    use Queueable;

    public string $titulo;
    public string $url;
    public int $adivinhacaoId;

    public function __construct(string $titulo, string $url, int $adivinhacaoId)
    {
        $this->titulo = $titulo;
        $this->url = $url;
        $this->adivinhacaoId = $adivinhacaoId;
    }

    public function handle(): void
    {
        User::select('users.email', 'users.id')->leftJoin('emails_bloqueados', 'emails_bloqueados.email', '=', 'users.email')->whereNull('emails_bloqueados.id')->where('banned', false)->chunk(100, function ($usuarios) {
            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)
                    ->queue((new NotifyNewAdivinhacaoMail($this->titulo, $this->url)));
                DB::table('notificacoes')->insert([
                    'user_id' => $usuario->id,
                    'adivinhacao_id' => $this->adivinhacaoId,
                    'tipo' => 'email',
                    'sent_at' => now(),
                ]);
            }
        });
    }
}
