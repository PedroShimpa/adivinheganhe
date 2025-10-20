<?php

namespace App\Exports;

use App\Models\AdivinhacoesRespostas;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RespostasExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return AdivinhacoesRespostas::query()
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
