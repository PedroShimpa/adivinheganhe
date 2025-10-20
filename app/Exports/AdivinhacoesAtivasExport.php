<?php

namespace App\Exports;

use App\Models\Adivinhacoes;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdivinhacoesAtivasExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Adivinhacoes::query()
            ->select('adivinhacoes.uuid', 'adivinhacoes.created_at', 'adivinhacoes.titulo', 'users.name', 'users.username')
            ->join('users', 'users.id', '=', 'adivinhacoes.user_id')
            ->whereNull('regiao_id')
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
            ->orderBy('adivinhacoes.id', 'desc');
    }

    public function headings(): array
    {
        return [
            'Código',
            'Data de criação',
            'Título',
            'Nome do Criador',
            'Username do Criador',
        ];
    }
}
