<?php

namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesPremiacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\Comment;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\Post;
use App\Models\User;
use App\DataTables\PremiacoesDataTable;
use App\DataTables\ComentariosDataTable;
use App\DataTables\AdivinhacoesAtivasDataTable;
use App\DataTables\RespostasDataTable;
use App\DataTables\UsersDataTable;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $adivinhacoesAtivas = (new Adivinhacoes())->getAtivas(true);
        $data = [
            'countUsers' => User::where('banned', false)->count(),
            'countAdivinhacoes' => Adivinhacoes::count(),
            'countAdivinhacoesAtivas' => $adivinhacoesAtivas->count(),
            'countPosts' => Post::count(),
            'countRespostasClassico' => AdivinhacoesRespostas::count(),
            'countRespostasClassicoToday' => AdivinhacoesRespostas::whereDate('created_at', today())->count(),
            'countJogosAdivinheOmilhao' => InicioJogo::count(),
            'countJogosAdivinheOmilhaoToday' => InicioJogo::whereDate('created_at', today())->count(),
            'countPartidasCompetitivo' => Partidas::count(),
            'countPartidasCompetitivoToday' => Partidas::whereDate('created_at', today())->count(),
            'jogadoresNaFilaAgoraCompetitivo' => Fila::count(),
            'premiacoesTable' => app(PremiacoesDataTable::class)->html(),
            'comentariosTable' => app(ComentariosDataTable::class)->html(),
            'adivinhacoesAtivasTable' => app(AdivinhacoesAtivasDataTable::class)->html(),
            'respostasTable' => app(RespostasDataTable::class)->html(),
            'usersTable' => app(UsersDataTable::class)->html(),
        ];
        return view('dashboard.index')->with($data);
    }

    public function premiacoesData(PremiacoesDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function comentariosData(ComentariosDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function adivinhacoesAtivasData(AdivinhacoesAtivasDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function respostasData(RespostasDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function usersData(UsersDataTable $dataTable)
    {
        return $dataTable->ajax();
    }
}
