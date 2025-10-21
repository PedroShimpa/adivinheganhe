<?php

namespace App\Exports;

use App\Models\AdivinhacoesPremiacoes;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PremiacoesExport implements FromQuery, WithHeadings
{
    public function query()
    {
        $query = AdivinhacoesPremiacoes::query()
            ->select('adivinhacoes_premiacoes.id', 'adivinhacoes_premiacoes.created_at', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderBy('adivinhacoes_premiacoes.id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('adivinhacoes_premiacoes.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Data',
            'Nome',
            'Usuário',
            'Título',
        ];
    }
}
