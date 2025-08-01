<?php

namespace App\Jobs;

use App\Mail\NotifyNewAdivinhacaoMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EnviarNotificacaoNovaAdivinhacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $titulo;
    public string $url;

    public function __construct(string $titulo, string $url)
    {
        $this->titulo = $titulo;
        $this->url = $url;
    }

    public function handle(): void
    {
        User::select('email')->leftJoin('emails_bloqueados', 'emails_bloqueados.email', '=', 'users.email')->whereNull('emails_bloqueados.id')->chunk(100, function ($usuarios) {
            foreach ($usuarios as $usuario) {
                Mail::to($usuario->email)
                    ->queue(new NotifyNewAdivinhacaoMail($this->titulo, $this->url));
            }
        });
    }
}
