<?php

namespace App\Jobs;

use App\Models\ChatMessages;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class IncluirMensagemChat implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $data) {}

    public function handle(): void
    {
        ChatMessages::create($this->data);
    }
}
