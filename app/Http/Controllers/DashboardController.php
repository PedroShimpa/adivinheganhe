<?php
namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\User;
use Illuminate\Support\Facades\Redis;
use Google\Client;
use Illuminate\Support\Facades\Http;
use App\DataTables\PremiacoesDataTable;
use App\DataTables\ComentariosDataTable;
use App\DataTables\AdivinhacoesAtivasDataTable;
use App\DataTables\RespostasDataTable;
use App\DataTables\UsersDataTable;

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

        // Online users
        $onlineUsers = $this->getOnlineUsers();

        // Google AdSense earnings
        $adsenseEarnings = $this->getAdSenseEarnings();

        $data = [
            'countUsers' => User::where('banned', false)->count(),
            'countUsersToday' => User::where('banned', false)->whereDate('created_at', today())->count(),
            'countUsersOnline' => $onlineUsers['count'],
            'onlineUsers' => $onlineUsers['users'],
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
            'adsenseEarnings' => $adsenseEarnings,
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

    private function getOnlineUsers()
    {
        try {
            // Get all online user keys from Redis
            $onlineKeys = Redis::keys('online_user_*');
            $onlineUserIds = array_map(function ($key) {
                return str_replace('online_user_', '', $key);
            }, $onlineKeys);

            $count = count($onlineUserIds);

            // Get user details for the list
            $users = [];
            if ($count > 0) {
                $users = User::whereIn('id', $onlineUserIds)->where('banned', false)->get(['id', 'name', 'email']);
            }

            return [
                'count' => $count,
                'users' => $users
            ];
        } catch (\Exception $e) {
            return [
                'count' => 0,
                'users' => collect()
            ];
        }
    }

    private function getAdSenseEarnings()
    {
        try {
            $jsonPath = env('GOOGLE_ADSENSE_JSON_PATH');

            if (!$jsonPath || !file_exists($jsonPath)) {
                return [
                    'today' => '0.00',
                    'thisMonth' => '0.00',
                    'error' => 'Arquivo JSON do AdSense não encontrado.'
                ];
            }

            $client = new Client();
            $client->setAuthConfig($jsonPath);
            $client->addScope('https://www.googleapis.com/auth/adsense.readonly');
            $client->useApplicationDefaultCredentials();

            $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

            // Fetch AdSense accounts to get account ID
            $accountsResponse = Http::withToken($accessToken)
                ->get('https://adsense.googleapis.com/v2/accounts');

            if ($accountsResponse->failed()) {
                throw new \Exception('Failed to fetch AdSense accounts: ' . $accountsResponse->body());
            }

            $accounts = $accountsResponse->json();
            if (empty($accounts['accounts'])) {
                throw new \Exception('No AdSense accounts found.');
            }

            $accountId = $accounts['accounts'][0]['name']; // e.g., "accounts/pub-XXXXXXXXXXXXXXXX"

            // Today's earnings
            $today = now()->format('Y-m-d');
            $todayReportResponse = Http::withToken($accessToken)
                ->get("https://adsense.googleapis.com/v2/{$accountId}/reports:generate", [
                    'dateRange' => 'TODAY',
                    'metrics' => ['ESTIMATED_EARNINGS'],
                    'dimensions' => ['DATE'],
                    'reportingTimeZone' => 'ACCOUNT_TIME_ZONE'
                ]);

            if ($todayReportResponse->failed()) {
                throw new \Exception('Failed to fetch today\'s earnings: ' . $todayReportResponse->body());
            }

            $todayReport = $todayReportResponse->json();
            $todayEarnings = 0;
            if (!empty($todayReport['rows'])) {
                $todayEarnings = $todayReport['rows'][0]['cells'][1]['value'];
            }

            // This month's earnings
            $startOfMonth = now()->startOfMonth()->format('Y-m-d');
            $endOfMonth = now()->endOfMonth()->format('Y-m-d');

            $monthReportResponse = Http::withToken($accessToken)
                ->get("https://adsense.googleapis.com/v2/{$accountId}/reports:generate", [
                    'dateRange' => 'CUSTOM',
                    'startDate.day' => (int)now()->startOfMonth()->format('d'),
                    'startDate.month' => (int)now()->startOfMonth()->format('m'),
                    'startDate.year' => (int)now()->startOfMonth()->format('Y'),
                    'endDate.day' => (int)now()->endOfMonth()->format('d'),
                    'endDate.month' => (int)now()->endOfMonth()->format('m'),
                    'endDate.year' => (int)now()->endOfMonth()->format('Y'),
                    'metrics' => ['ESTIMATED_EARNINGS'],
                    'dimensions' => ['DATE'],
                    'reportingTimeZone' => 'ACCOUNT_TIME_ZONE'
                ]);

            if ($monthReportResponse->failed()) {
                throw new \Exception('Failed to fetch this month\'s earnings: ' . $monthReportResponse->body());
            }

            $monthReport = $monthReportResponse->json();
            $thisMonthEarnings = 0;
            if (!empty($monthReport['rows'])) {
                foreach ($monthReport['rows'] as $row) {
                    $thisMonthEarnings += $row['cells'][1]['value'];
                }
            }

            return [
                'today' => number_format($todayEarnings, 2, ',', '.'),
                'thisMonth' => number_format($thisMonthEarnings, 2, ',', '.'),
                'error' => null
            ];
        } catch (\Exception $e) {
            return [
                'today' => '0.00',
                'thisMonth' => '0.00',
                'error' => 'Erro ao obter dados do AdSense: ' . $e->getMessage()
            ];
        }
    }
}
