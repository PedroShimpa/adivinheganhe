<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OnlineUsersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $onlineUsers = Cache::get('online_users', []);
        $userIds = array_keys($onlineUsers);

        $users = User::whereIn('id', $userIds)
            ->where('banned', false)
            ->orderByRaw('FIELD(id, ' . implode(',', $userIds) . ')')
            ->get(['id', 'name', 'username', 'email']);

        return $users->map(function ($user) use ($onlineUsers) {
            $lastActivity = $onlineUsers[$user->id] ?? null;
            $timeOnline = $lastActivity ? floor((now()->timestamp - $lastActivity) / 60) . ' min atrás' : 'N/A';

            return [
                'ID' => $user->id,
                'Nome' => $user->name,
                'Username' => $user->username,
                'Email' => $user->email,
                'Tempo Online' => $timeOnline,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Username',
            'Email',
            'Tempo Online',
        ];
    }
}
