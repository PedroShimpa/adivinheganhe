<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PartidaFinalizada;
use App\Models\Competitivo\Partidas;
use App\Models\Competitivo\Respostas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MonitorarPartidas extends Command
{
    protected $signature = 'competitivo:monitorar';
    protected $description = 'Monitora partidas competitivas e finaliza se o tempo expirar';

    public function handle()
    {
        try {
            $partidas = Partidas::where('status', 1)->get();

            foreach ($partidas as $partida) {
                try {
                    $roundAtual = $partida->round_atual;
                    $tempoMax = max(100 - ($roundAtual - 1) * 10, 10);
                    $inicioRound = $partida->round_started_at;

                    if (!$inicioRound) {
                        Log::warning("Partida {$partida->id} não tem round_started_at definido.");
                        continue;
                    }

                    if ($inicioRound->diffInSeconds(Carbon::now(), false) > $tempoMax) {
                        Log::info("Finalizando partida {$partida->id} devido ao tempo expirado.");

                        $respostas = Respostas::where('partida_id', $partida->id)
                            ->where('round_atual', $roundAtual)
                            ->get();

                        $jogadoresPartida = $partida->jogadores()->pluck('user_id')->toArray();

                        if (count($jogadoresPartida) !== 2) {
                            Log::error("Partida {$partida->id} não tem exatamente 2 jogadores.");
                            continue;
                        }

                        $vencedorId = null;
                        $perdedorId = null;

                        if ($respostas->count() === 0) {
                            // Nenhum respondeu - talvez empate ou nenhum vencedor
                            Log::info("Nenhum jogador respondeu na partida {$partida->id}.");
                        } elseif ($respostas->count() === 1) {
                            // Apenas um respondeu - ele vence
                            $vencedorId = $respostas->first()->user_id;
                            $perdedorId = collect($jogadoresPartida)->first(fn($id) => $id != $vencedorId);
                        } elseif ($respostas->count() === 2) {
                            // Ambos responderam - verificar quem acertou primeiro ou algo similar
                            // Assumindo que 'correta' indica se acertou
                            $respostaCorreta = $respostas->where('correta', 1)->first();
                            if ($respostaCorreta) {
                                $vencedorId = $respostaCorreta->user_id;
                                $perdedorId = collect($jogadoresPartida)->first(fn($id) => $id != $vencedorId);
                            } else {
                                // Ambos erraram - talvez empate
                                Log::info("Ambos erraram na partida {$partida->id}.");
                            }
                        }

                        if ($vencedorId) {
                            // Marca o vencedor
                            $partida->jogadores()->where('user_id', $vencedorId)->update(['vencedor' => 1]);
                            Log::info("Vencedor definido: {$vencedorId} na partida {$partida->id}.");

                            // Atualiza elo do vencedor
                            $userVencedor = User::find($vencedorId);
                            if ($userVencedor) {
                                $rankVencedor = $userVencedor->getOrCreateRank();
                                $rankVencedor->elo += env('PONTOS_COMPETITIVO', 5);
                                $rankVencedor->save();
                            }

                            // Atualiza elo do perdedor se houver
                            if ($perdedorId) {
                                $userPerdedor = User::find($perdedorId);
                                if ($userPerdedor) {
                                    $rankPerdedor = $userPerdedor->getOrCreateRank();
                                    $rankPerdedor->elo -= env('PONTOS_COMPETITIVO', 5);
                                    $rankPerdedor->save();
                                }
                            }
                        }

                        $partida->status = 2; // finalizada
                        $partida->save();

                        broadcast(new PartidaFinalizada($partida->uuid));
                        Log::info("Partida {$partida->id} finalizada.");
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao processar partida {$partida->id}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error("Erro geral no comando MonitorarPartidas: " . $e->getMessage());
        }
    }
}
