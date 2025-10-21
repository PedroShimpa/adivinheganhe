<?php

namespace App\DataTables;

use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ComentariosDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d/m/Y H:i');
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Comment $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->select('comments.id', 'comments.created_at', 'comments.body', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'comments.commentable_id')
            ->join('users', 'users.id', '=', 'comments.user_id')
            ->where('commentable_type', 'App\Models\Adivinhacoes')
            ->where('adivinhacoes.resolvida', 'N')
            ->where('adivinhacoes.exibir_home', 'S')
            ->where(function ($q) {
                $q->where('adivinhacoes.expire_at', '>', now());
                $q->orWhereNull('adivinhacoes.expire_at');
            })
            ->where(function ($q) {
                $q->where('adivinhacoes.liberado_at', '<=', now());
                $q->orWhereNull('adivinhacoes.liberado_at');
            })
            ->orderBy('comments.id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('comments.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('comentariosTable')
                    ->columns($this->getColumns())
                    ->ajax(route('dashboard.comentarios.data'))
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel')->text('<i class="fas fa-file-excel"></i> Excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('created_at')->title('Data'),
            Column::make('name')->title('Nome'),
            Column::make('username')->title('Usuario'),
            Column::make('titulo')->title('Adivinhação'),
            Column::make('body')->title('Comentario'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Comentarios_' . date('YmdHis');
    }
}
