<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PartidaFinalizada;
use App\Models\Competitivo\Partidas;
use App\Models\Competitivo\PartidasJogadores;
use App\Models\Competitivo\Respostas;
use Carbon\Carbon;

class MonitorarPartidas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'competitivo:monitorar';

    protected $description = 'Monitora partidas competitivas e finaliza se o tempo expirar';

    public function handle()
    {
        $partidas = Partidas::where('status', 1)->get();

        foreach ($partidas as $partida) {
            
            $roundAtual = $partida->round_atual;
            $tempoMax = max(100 - ($roundAtual - 1) * 10, 10);
            $inicioRound = $partida->round_started_at;
            
            
            if ($inicioRound->diffInSeconds(Carbon::now(), false)> $tempoMax) {

                $respostas = Respostas::where('partida_id', $partida->id)->where('round_atual', $roundAtual)->get();

                if ($respostas->count() === 1) {
                    $vencedorId = $respostas->first()->user_id;
                    PartidasJogadores::where('partida_id', $partida->id)
                        ->where('user_id', $vencedorId)
                        ->update(['vencedor' => 1]);
                }

                $partida->status = 2;
                $partida->save();

                broadcast(new PartidaFinalizada($partida->uuid));
            }
        }
    }
}
