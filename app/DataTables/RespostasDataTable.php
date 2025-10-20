<?php

namespace App\DataTables;

use App\Models\AdivinhacoesRespostas;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RespostasDataTable extends DataTable
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
    public function query(AdivinhacoesRespostas $model): QueryBuilder
    {
        return $model->newQuery()
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

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('respostasTable')
                    ->columns($this->getColumns())
                    ->ajax(route('dashboard.respostas.data'))
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
            Column::make('username')->title('Usuário'),
            Column::make('titulo')->title('Título'),
            Column::make('resposta')->title('Resposta'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Respostas_' . date('YmdHis');
    }
}
