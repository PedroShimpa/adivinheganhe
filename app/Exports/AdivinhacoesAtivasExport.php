<?php

namespace App\Exports;

use App\Models\Adivinhacoes;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdivinhacoesAtivasExport implements FromQuery, WithHeadings
{
    public function query()
    {
        $query = Adivinhacoes::query()
            ->select('adivinhacoes.uuid', 'adivinhacoes.created_at', 'adivinhacoes.titulo')
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

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('adivinhacoes.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Data de criação',
            'Título',
        ];
    }
}
