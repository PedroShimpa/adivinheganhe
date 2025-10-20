<?php

namespace App\Exports;

use App\Models\Comment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ComentariosExport implements FromQuery, WithHeadings
{
    public function query()
    {
        return Comment::query()
            ->select('comments.id', 'comments.created_at', 'comments.body', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'comments.commentable_id')
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->where('commentable_type', 'App\Models\Adivinhacoes')
            ->orderBy('comments.id', 'desc');
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
