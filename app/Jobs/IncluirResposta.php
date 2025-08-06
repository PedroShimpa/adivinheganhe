<?php

namespace App\Jobs;

use App\Models\AdivinhacoesRespostas;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class IncluirResposta implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private array $data)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        AdivinhacoesRespostas::insert($this->data);
    }
}
