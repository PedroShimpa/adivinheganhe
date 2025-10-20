<?php

namespace App\Exports;

use App\Models\AdivinhacoesPremiacoes;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PremiacoesExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return AdivinhacoesPremiacoes::query()
            ->select('adivinhacoes_premiacoes.id', 'adivinhacoes_premiacoes.created_at', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderBy('adivinhacoes_premiacoes.id', 'desc');
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
