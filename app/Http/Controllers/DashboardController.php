<?php
namespace App\Http\Controllers;

use App\Models\Adivinhacoes;
use App\Models\AdivinhacoesRespostas;
use App\Models\AdivinheOMilhao\InicioJogo;
use App\Models\Competitivo\Fila;
use App\Models\Competitivo\Partidas;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
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
        try {
            $adivinhacoesAtivas = (new Adivinhacoes())->getAtivas(true);
            $countAdivinhacoesAtivas = is_array($adivinhacoesAtivas) ? count($adivinhacoesAtivas) : $adivinhacoesAtivas->count();
        } catch (\Exception $e) {
            \Log::error('Error getting active adivinhacoes: ' . $e->getMessage());
            $countAdivinhacoesAtivas = 0;
        }

        // Horarios com mais respostas
        try {
            $horariosRaw = AdivinhacoesRespostas::selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->pluck('count', 'hour');
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
        } catch (\Exception $e) {
            \Log::error('Error getting dias semana respostas: ' . $e->getMessage());
            $diasSemanaRespostas = [
                'Domingo' => 0, 'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0,
                'Quinta' => 0, 'Sexta' => 0, 'Sábado' => 0
            ];
        }

        // Online users
        $onlineUsers = $this->getOnlineUsers();

        // Google AdSense earnings
        $adsenseEarnings = $this->getAdSenseEarnings();

        try {
            $data = [
                'countUsers' => User::where('banned', false)->count(),
                'countUsersToday' => User::where('banned', false)->whereDate('created_at', today())->count(),
                'countUsersOnline' => $onlineUsers['count'],
                'onlineUsers' => $onlineUsers,
                'countAdivinhacoes' => Adivinhacoes::count(),
                'countAdivinhacoesAtivas' => $countAdivinhacoesAtivas,
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
                'horariosRespostas' => array_fill(0, 24, 0),
                'diasSemanaRespostas' => [
                    'Domingo' => 0, 'Segunda' => 0, 'Terça' => 0, 'Quarta' => 0,
                    'Quinta' => 0, 'Sexta' => 0, 'Sábado' => 0
                ],
                'adsenseEarnings' => $adsenseEarnings,
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

    private function getAdSenseEarnings()
    {
        try {
            $jsonPath = env('GOOGLE_ADSENSE_JSON_PATH');

            if (!$jsonPath || !file_exists($jsonPath)) {
                \Log::warning('AdSense JSON file not found at path: ' . $jsonPath);
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

            // Use account ID from environment variable
            $accountId = env('GOOGLE_ADSENSE_ACCOUNT_ID');

            if (!$accountId) {
                \Log::warning('GOOGLE_ADSENSE_ACCOUNT_ID not set in environment variables');
                throw new \Exception('AdSense account ID not configured. Please set GOOGLE_ADSENSE_ACCOUNT_ID in your .env file.');
            }

            // Validate account ID format
            if (!preg_match('/^accounts\/pub-\d+$/', $accountId)) {
                \Log::warning('Invalid AdSense account ID format: ' . $accountId);
                throw new \Exception('Invalid AdSense account ID format. Expected format: accounts/pub-XXXXXXXXXXXXXXXX');
            }

            // Today's earnings
            $todayReportResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->retry(3, 100)
                ->get("https://adsense.googleapis.com/v2/{$accountId}/reports:generate", [
                    'dateRange' => 'TODAY',
                    'metrics' => 'ESTIMATED_EARNINGS',
                    'dimensions' => 'DATE',
                    'reportingTimeZone' => 'ACCOUNT_TIME_ZONE'
                ]);

            if ($todayReportResponse->failed()) {
                \Log::error('AdSense API today earnings request failed', [
                    'status' => $todayReportResponse->status(),
                    'body' => $todayReportResponse->body()
                ]);
                throw new \Exception('Failed to fetch today\'s earnings: HTTP ' . $todayReportResponse->status());
            }

            $todayReport = $todayReportResponse->json();
            $todayEarnings = 0;
            if (!empty($todayReport['rows'])) {
                $todayEarnings = $todayReport['rows'][0]['cells'][1]['value'];
            }

            // This month's earnings
            $monthReportResponse = Http::withToken($accessToken)
                ->timeout(30)
                ->retry(3, 100)
                ->get("https://adsense.googleapis.com/v2/{$accountId}/reports:generate", [
                    'dateRange' => 'CUSTOM',
                    'startDate.day' => (int)now()->startOfMonth()->format('d'),
                    'startDate.month' => (int)now()->startOfMonth()->format('m'),
                    'startDate.year' => (int)now()->startOfMonth()->format('Y'),
                    'endDate.day' => (int)now()->endOfMonth()->format('d'),
                    'endDate.month' => (int)now()->endOfMonth()->format('m'),
                    'endDate.year' => (int)now()->endOfMonth()->format('Y'),
                    'metrics' => 'ESTIMATED_EARNINGS',
                    'dimensions' => 'DATE',
                    'reportingTimeZone' => 'ACCOUNT_TIME_ZONE'
                ]);

            if ($monthReportResponse->failed()) {
                \Log::error('AdSense API month earnings request failed', [
                    'status' => $monthReportResponse->status(),
                    'body' => $monthReportResponse->body()
                ]);
                throw new \Exception('Failed to fetch this month\'s earnings: HTTP ' . $monthReportResponse->status());
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
            \Log::error('AdSense earnings fetch failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'today' => '0.00',
                'thisMonth' => '0.00',
                'error' => 'Erro ao obter dados do AdSense: ' . $e->getMessage()
            ];
        }
    }
}
