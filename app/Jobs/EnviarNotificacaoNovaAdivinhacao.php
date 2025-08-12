<?php

namespace App\Jobs;

use App\Mail\NotifyNewAdivinhacaoMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnviarNotificacaoNovaAdivinhacao implements ShouldQueue
{
    use Queueable;

    public string $titulo;
    public string $url;

    public function __construct(string $titulo, string $url)
    {
        $this->titulo = $titulo;
        $this->url = $url;
    }

    public function handle(): void
    {
        User::select('users.email')->leftJoin('emails_bloqueados', 'emails_bloqueados.email', '=', 'users.email')->whereNull('emails_bloqueados.id')->chunk(100, function ($usuarios) {
            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)
                    ->queue(new NotifyNewAdivinhacaoMail($this->titulo, $this->url));
            }
        });
    }
}
