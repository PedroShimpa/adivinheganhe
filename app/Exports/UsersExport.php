<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithHeadings, WithMapping
{
    public function query()
    {
        return User::query()->where('banned', false);
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nome',
            'Username',
            'Email',
            'CPF',
            'WhatsApp',
            'Indicado por',
            'VIP até',
            'Criado em',
            'Atualizado em',
        ];
    }

    public function map($user): array
    {
        $indicatedBy = 'N/A';
        if ($user->indicated_by) {
            $indicatedUser = User::where('uuid', $user->indicated_by)->first();
            $indicatedBy = $indicatedUser ? $indicatedUser->name . ' (' . $indicatedUser->username . ')' : 'N/A';
        }

        return [
            $user->id,
            $user->name,
            $user->username,
            $user->email,
            $user->cpf ?? '',
            $user->whatsapp ?? '',
            $indicatedBy,
            $user->membership_expires_at ? $user->membership_expires_at->format('d/m/Y H:i') : '',
            $user->created_at->format('d/m/Y H:i'),
            $user->updated_at->format('d/m/Y H:i'),
        ];
    }
}
