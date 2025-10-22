<?php
namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use App\DataTables\PremiacoesDataTable;
use App\DataTables\ComentariosDataTable;
use App\DataTables\AdivinhacoesAtivasDataTable;
use App\DataTables\RespostasDataTable;
use App\DataTables\UsersDataTable;
use App\Exports\UsersExport;
use App\Exports\PremiacoesExport;
use App\Exports\ComentariosExport;
use App\Exports\AdivinhacoesAtivasExport;
use App\Exports\RespostasExport;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $startDate = request('start_date');
        $endDate = request('end_date');

        $dateFilter = [];
        if ($startDate && $endDate) {
            $dateFilter = [
                'start' => $startDate . ' 00:00:00',
                'end' => $endDate . ' 23:59:59'
            ];
        }

        try {
            $adivinhacoesAtivas = (new Adivinhacoes())->getAtivas(true);
            $countAdivinhacoesAtivas = is_array($adivinhacoesAtivas) ? count($adivinhacoesAtivas) : $adivinhacoesAtivas->count();
        } catch (\Exception $e) {
            \Log::error('Error getting active adivinhacoes: ' . $e->getMessage());
            $countAdivinhacoesAtivas = 0;
        }

        // Horarios com mais respostas
        try {
            $query = AdivinhacoesRespostas::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour');
            if (!empty($dateFilter)) {
                $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
            }
            $horariosRaw = $query->pluck('count', 'hour');
            $horariosRespostas = array_fill(0, 24, 0);
            foreach ($horariosRaw as $hour => $count) {
                $horariosRespostas[$hour] = $count;
            }
        } catch (\Exception $e) {
            \Log::error('Error getting horarios respostas: ' . $e->getMessage());
            $horariosRespostas = array_fill(0, 24, 0);
        }

        // Dias da semana com mais respostas
        try {
            $query = AdivinhacoesRespostas::selectRaw('DAYOFWEEK(created_at) as dia, COUNT(*) as count')
                ->groupBy('dia');
            if (!empty($dateFilter)) {
                $query->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
            }
            $diasRaw = $query->pluck('count', 'dia');
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
        } catch (\Exception $e) {
            \Log::error('Error getting dias semana respostas: ' . $e->getMessage());
            $diasSemanaRespostas = [
                'Domingo' => 0, 'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0,
                'Quinta' => 0, 'Sexta' => 0, 'Sábado' => 0
            ];
        }

        // Online users
        $onlineUsers = $this->getOnlineUsers();

        // Top 10 users who indicated others
        try {
            $query = User::select('users.name', 'users.username', \DB::raw('COUNT(adicionais_indicacao.id) as total_indicados'))
                ->join('adicionais_indicacao', 'users.uuid', '=', 'adicionais_indicacao.user_uuid')
                ->groupBy('users.id', 'users.name', 'users.username')
                ->orderBy('total_indicados', 'desc')
                ->limit(10);
            if (!empty($dateFilter)) {
                $query->whereBetween('adicionais_indicacao.created_at', [$dateFilter['start'], $dateFilter['end']]);
            }
            $topIndicadores = $query->get()->toArray();
        } catch (\Exception $e) {
            \Log::error('Error getting top indicadores: ' . $e->getMessage());
            $topIndicadores = [];
        }

        try {
            $userQuery = User::where('banned', false);
            $respostasQuery = AdivinhacoesRespostas::query();
            $inicioJogoQuery = InicioJogo::query();
            $partidasQuery = Partidas::query();
            $suporteQuery = \App\Models\Suporte::where('status', 'A');

            if (!empty($dateFilter)) {
                $userQuery->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
                $respostasQuery->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
                $inicioJogoQuery->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
                $partidasQuery->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
                $suporteQuery->whereBetween('created_at', [$dateFilter['start'], $dateFilter['end']]);
            }

            $data = [
                'countUsers' => $userQuery->count(),
                'countUsersToday' => User::where('banned', false)->whereDate('created_at', today())->count(),
                'countUsersOnline' => $onlineUsers['count'],
                'onlineUsers' => $onlineUsers,
                'countAdivinhacoes' => Adivinhacoes::count(),
                'countAdivinhacoesAtivas' => $countAdivinhacoesAtivas,
                'countRespostasClassico' => $respostasQuery->count(),
                'countRespostasClassicoToday' => AdivinhacoesRespostas::whereDate('created_at', today())->count(),
                'countJogosAdivinheOmilhao' => $inicioJogoQuery->count(),
                'countJogosAdivinheOmilhaoToday' => InicioJogo::whereDate('created_at', today())->count(),
                'countPartidasCompetitivo' => $partidasQuery->count(),
                'countPartidasCompetitivoToday' => Partidas::whereDate('created_at', today())->count(),
                'jogadoresNaFilaAgoraCompetitivo' => Fila::count(),
                'countChamadosAguardando' => $suporteQuery->count(),
                'countVipUsers' => User::where('banned', false)->whereNotNull('membership_expires_at')->where('membership_expires_at', '>', now())->count(),
                'horariosRespostas' => $horariosRespostas,
                'diasSemanaRespostas' => $diasSemanaRespostas,
                'topIndicadores' => $topIndicadores,
                'premiacoesTable' => app(PremiacoesDataTable::class)->html(),
                'comentariosTable' => app(ComentariosDataTable::class)->html(),
                'adivinhacoesAtivasTable' => app(AdivinhacoesAtivasDataTable::class)->html(),
                'respostasTable' => app(RespostasDataTable::class)->html(),
                'usersTable' => app(UsersDataTable::class)->html(),
            ];
        } catch (\Exception $e) {
            \Log::error('Error preparing dashboard data: ' . $e->getMessage());
            // Provide fallback data
            $data = [
                'countUsers' => 0,
                'countUsersToday' => 0,
                'countUsersOnline' => 0,
                'onlineUsers' => ['count' => 0, 'users' => collect()],
                'countAdivinhacoes' => 0,
                'countAdivinhacoesAtivas' => 0,
                'countRespostasClassico' => 0,
                'countRespostasClassicoToday' => 0,
                'countJogosAdivinheOmilhao' => 0,
                'countJogosAdivinheOmilhaoToday' => 0,
                'countPartidasCompetitivo' => 0,
                'countPartidasCompetitivoToday' => 0,
                'jogadoresNaFilaAgoraCompetitivo' => 0,
                'countVipUsers' => 0,
                'horariosRespostas' => array_fill(0, 24, 0),
                'diasSemanaRespostas' => [
                    'Domingo' => 0, 'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0,
                    'Quinta' => 0, 'Sexta' => 0, 'Sábado' => 0
                ],
                'premiacoesTable' => '',
                'comentariosTable' => '',
                'adivinhacoesAtivasTable' => '',
                'respostasTable' => '',
                'usersTable' => '',
            ];
        }

        return view('dashboard.index')->with($data);
    }

    public function premiacoesData(PremiacoesDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function premiacaoModal($id)
    {
        $premiacao = \App\Models\AdivinhacoesPremiacoes::findOrFail($id);

        return view('dashboard.partials.premiacao-modal', compact('premiacao'));
    }

    public function banModal($id)
    {
        $user = User::findOrFail($id);

        return view('dashboard.partials.ban-modal', compact('user'));
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

    public function vipUsers()
    {
        $vipUsers = User::where('banned', false)
            ->whereNotNull('membership_expires_at')
            ->where('membership_expires_at', '>', now())
            ->orderBy('membership_expires_at', 'asc')
            ->get();

        return view('admin.vip-users', compact('vipUsers'));
    }

    public function exportUsers()
    {
        return Excel::download(new UsersExport, 'usuarios.xlsx');
    }

    public function exportPremiacoes()
    {
        return Excel::download(new PremiacoesExport, 'premiacoes.xlsx');
    }

    public function exportComentarios()
    {
        return Excel::download(new ComentariosExport, 'comentarios.xlsx');
    }

    public function exportAdivinhacoesAtivas()
    {
        return Excel::download(new AdivinhacoesAtivasExport, 'adivinhacoes_ativas.xlsx');
    }

    public function exportRespostas()
    {
        return Excel::download(new RespostasExport, 'respostas.xlsx');
    }



    private function getOnlineUsers()
    {
        try {
            // Use a simpler approach: track online users via cache with TTL
            $onlineUsersKey = 'online_users';
            $fiveMinutes = 300; // 5 minutes in seconds

            // Get current online users from cache
            $onlineUsers = Cache::get($onlineUsersKey, []);

            // Clean up expired users
            $currentTime = now()->timestamp;
            $activeUsers = [];
            foreach ($onlineUsers as $userId => $lastActivity) {
                if (($currentTime - $lastActivity) <= $fiveMinutes) {
                    $activeUsers[$userId] = $lastActivity;
                }
            }

            // Update cache with cleaned data
            Cache::put($onlineUsersKey, $activeUsers, $fiveMinutes);

            $count = count($activeUsers);

            // Get user details for the list
            $users = [];
            if ($count > 0) {
                $userIds = array_keys($activeUsers);
                $users = User::whereIn('id', $userIds)
                    ->where('banned', false)
                    ->orderByRaw('FIELD(id, ' . implode(',', $userIds) . ')') // Maintain order
                    ->get(['id', 'name', 'email'])
                    ->toArray();
            }

            return [
                'count' => $count,
                'users' => $users
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting online users: ' . $e->getMessage());
            return [
                'count' => 0,
                'users' => collect()
            ];
        }
    }


}
