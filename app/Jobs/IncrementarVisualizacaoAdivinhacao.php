<?php

namespace App\Jobs;

namespace App\Jobs;

use App\Models\Adivinhacoes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class IncrementarVisualizacaoAdivinhacao implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $adivinhacaoId;

    public function __construct($adivinhacaoId)
    {
        $this->adivinhacaoId = $adivinhacaoId;
    }

    public function handle()
    {
        Adivinhacoes::where('id', $this->adivinhacaoId)->increment('visualizacoes');
    }
}
