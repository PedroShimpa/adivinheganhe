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

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitora partidas competitivas e finaliza se o tempo expirar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $partidas = Partidas::where('status', 1)->get();

        foreach ($partidas as $partida) {
            $roundAtual = $partida->round_atual;
            $tempoMax = max(100 - ($roundAtual - 1) * 10, 10); // segundos
            $inicioRound = $partida->round_started_at ?? $partida->updated_at;

            if (Carbon::now()->diffInSeconds($inicioRound) > $tempoMax) {

                // Busca respostas da rodada atual
                $respostas = Respostas::where('partida_id', $partida->id)
                    ->where('pergunta_id', $partida->pergunta_atual_id)
                    ->get();

                if ($respostas->isNotEmpty()) {
                    // Primeiro a responder vence
                    $vencedorId = $respostas->first()->user_id;

                    PartidasJogadores::where('partida_id', $partida->id)
                        ->where('user_id', $vencedorId)
                        ->update(['vencedor' => 1]);
                }

                // Finaliza a partida
                $partida->status = 2;
                $partida->finalizada_em = now();
                $partida->save();

                broadcast(new PartidaFinalizada($partida->uuid));
                $this->info("Partida {$partida->id} finalizada pelo monitor.");
            }
        }
    }
}
