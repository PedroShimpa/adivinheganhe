<?php

namespace App\Exports;

use App\Models\Comment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComentariosExport implements FromQuery, WithHeadings
{
    public function query()
    {
        $query = Comment::query()
            ->select('comments.id', 'comments.created_at', 'comments.body', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'comments.commentable_id')
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->where('commentable_type', 'App\Models\Adivinhacoes')
            ->orderBy('comments.id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('comments.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Data',
            'Comentário',
            'Nome',
            'Usuário',
            'Adivinhação',
        ];
    }
}
