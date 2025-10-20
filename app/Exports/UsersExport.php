<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromQuery, WithHeadings
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
            'VIP até',
            'Criado em',
            'Atualizado em',
        ];
    }
}
