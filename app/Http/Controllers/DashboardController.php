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
use Illuminate\Support\Facades\DB;
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

        // Horarios com mais respostas
        $horariosRaw = AdivinhacoesRespostas::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour');
        $horariosRespostas = array_fill(0, 24, 0);
        foreach ($horariosRaw as $hour => $count) {
            $horariosRespostas[$hour] = $count;
        }

        // Dias da semana com mais respostas
        $diasRaw = AdivinhacoesRespostas::selectRaw('DAYOFWEEK(created_at) as dia, COUNT(*) as count')
            ->groupBy('dia')
            ->pluck('count', 'dia');
        $diasSemanaRespostas = [
            'Domingo' => 0, 'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0,
            'Quinta' => 0, 'Sexta' => 0, 'Sábado' => 0
        ];
        $portugueseDays = [1 => 'Domingo', 2 => 'Segunda', 3 => 'Terça', 4 => 'Quarta', 5 => 'Quinta', 6 => 'Sexta', 7 => 'Sábado'];
        foreach ($diasRaw as $diaNum => $count) {
            if (isset($portugueseDays[$diaNum])) {
                $diasSemanaRespostas[$portugueseDays[$diaNum]] = $count;
            }
        }

        $data = [
            'countUsers' => User::where('banned', false)->count(),
            'countUsersToday' => User::where('banned', false)->whereDate('created_at', today())->count(),
            'countAdivinhacoes' => Adivinhacoes::count(),
            'countAdivinhacoesAtivas' => $adivinhacoesAtivas->count(),
            'countRespostasClassico' => AdivinhacoesRespostas::count(),
            'countRespostasClassicoToday' => AdivinhacoesRespostas::whereDate('created_at', today())->count(),
            'countJogosAdivinheOmilhao' => InicioJogo::count(),
            'countJogosAdivinheOmilhaoToday' => InicioJogo::whereDate('created_at', today())->count(),
            'countPartidasCompetitivo' => Partidas::count(),
            'countPartidasCompetitivoToday' => Partidas::whereDate('created_at', today())->count(),
            'jogadoresNaFilaAgoraCompetitivo' => Fila::count(),
            'horariosRespostas' => $horariosRespostas,
            'diasSemanaRespostas' => $diasSemanaRespostas,
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
