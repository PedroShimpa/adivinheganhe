<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class IncluirMensagemChat implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $data) {}

    public function handle(): void
    {
        DB::table('chat_messages')->insert($this->data);
    }
}
