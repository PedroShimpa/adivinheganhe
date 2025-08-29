<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PartidaFinalizada;
use App\Models\Competitivo\Partidas;
use App\Models\Competitivo\Respostas;
use App\Models\User;
use Carbon\Carbon;

class MonitorarPartidas extends Command
{
    protected $signature = 'competitivo:monitorar';
    protected $description = 'Monitora partidas competitivas e finaliza se o tempo expirar';

    public function handle()
    {
        $partidas = Partidas::where('status', 1)->get();

        foreach ($partidas as $partida) {

            $roundAtual = $partida->round_atual;
            $tempoMax = max(100 - ($roundAtual - 1) * 10, 10);
            $inicioRound = $partida->round_started_at;

            if ($inicioRound->diffInSeconds(Carbon::now(), false) > $tempoMax) {

                $respostas = Respostas::where('partida_id', $partida->id)
                    ->where('round_atual', $roundAtual)
                    ->get();

                $jogadoresPartida = $partida->jogadores()->pluck('user_id')->toArray();

                if ($respostas->count() === 1) {
                    $vencedorId = $respostas->first()->user_id;

                    if ($vencedorId) {
                        // Marca o vencedor
                        $partida->jogadores()->where('user_id', $vencedorId)->update(['vencedor' => 1]);

                        // Identifica o perdedor (usuário que não respondeu)
                        $perdedorId = collect($jogadoresPartida)->first(fn($id) => $id != $vencedorId);

                        // Atualiza elo do vencedor
                        $userVencedor = User::find($vencedorId)->rank;
                        if ($userVencedor) {
                            $userVencedor->elo += env('PONTOS_COMPETITVO', 5);
                            $userVencedor->save();
                        }

                        // Atualiza elo do perdedor
                        $userPerdedor = User::find($perdedorId)->rank;
                        if ($userPerdedor) {
                            $userPerdedor->elo += env('PONTOS_COMPETITVO', 5);
                            $userPerdedor->save();
                        }
                    }
                }

                $partida->status = 2; // finalizada
                $partida->save();

                broadcast(new PartidaFinalizada($partida->uuid));
            }
        }
    }
}
