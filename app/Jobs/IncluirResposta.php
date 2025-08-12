<?php

namespace App\Jobs;

use App\Models\AdivinhacoesRespostas;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IncluirResposta implements ShouldQueue
{
    use Queueable;

    public function __construct(private array $data) {}

    public function handle(): void
    {
        AdivinhacoesRespostas::insertOrIgnore($this->data);
    }
}
