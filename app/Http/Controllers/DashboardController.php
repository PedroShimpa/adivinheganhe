<?php

namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\Post;
use App\Models\User;

class DashboardController extends Controller
{
    public function dashboard()
    {

        $adivinhacoesAtivas = (new Adivinhacoes())->getAtivas(true);
        $data = [
            'countUsers' => User::where('banned', false)->count(),
            'users' => User::where('banned', false)->get(),

            'countAdivinhacoes' => Adivinhacoes::count(),
            'countAdivinhacoesAtivas' => $adivinhacoesAtivas->count(),

            'adivinhacoesAtivas' => $adivinhacoesAtivas,

            'countPosts' => Post::count(),
            'countRespostasClassico' => AdivinhacoesRespostas::count(),
            'countRespostasClassicoToday' => AdivinhacoesRespostas::whereDate('created_at', today())->count(),

            'premiacoes' => AdivinhacoesPremiacoes::select('adivinhacoes_premiacoes.id', 'adivinhacoes_premiacoes.created_at', 'users.name', 'adivinhacoes.titulo')
                ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
                ->orderBy('adivinhacoes_premiacoes.id', 'desc')->get(),

            'countJogosAdivinheOmilhao' => InicioJogo::count(),
            'countJogosAdivinheOmilhaoToday' => InicioJogo::whereDate('created_at', today())->count(),

            'countPartidasCompetitivo' => Partidas::count(),
            'countPartidasCompetitivoToday' => Partidas::whereDate('created_at', today())->count(),

            'jogadoresNaFilaAgoraCompetitivo' => Fila::count(),

            'respostasAdivinhacoesAtivas' => AdivinhacoesRespostas::select('adivinhacoes_respostas.created_at', 'adivinhacoes_respostas.resposta', 'adivinhacoes.resposta as resposta_correta', 'adivinhacoes.titulo', 'users.name')
                ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_respostas.adivinhacao_id')->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
                ->where('resolvida', 'N')
                ->where('exibir_home', 'S')
                ->where(function ($q) {
                    $q->where('expire_at', '>', now());
                    $q->orWhereNull('expire_at');
                })
                ->where(function ($q) {
                    $q->where('liberado_at', '<=', now());
                    $q->orWhereNull('liberado_at');
                })
                ->orderBy('adivinhacoes_respostas.id', 'desc')
                ->get()

        ];
        return view('dashboard.index')->with($data);
    }
}
