<?php

namespace App\Exports;

use App\Models\AdivinhacoesRespostas;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RespostasExport implements FromQuery, WithHeadings
{
    public function query()
    {
        $query = AdivinhacoesRespostas::query()
            ->select('adivinhacoes_respostas.id', 'adivinhacoes_respostas.created_at', 'adivinhacoes_respostas.resposta', 'adivinhacoes.titulo', 'users.name', 'users.username')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_respostas.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_respostas.user_id')
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
            ->orderBy('adivinhacoes_respostas.id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('adivinhacoes_respostas.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Data',
            'Resposta',
            'Nome',
            'Usuário',
            'Título',
        ];
    }
}
