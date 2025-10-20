<?php

namespace App\DataTables;

use App\Models\AdivinhacoesPremiacoes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class PremiacoesDataTable extends DataTable
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
            ->addColumn('action', function($row) {
                $modal = '<div class="modal fade" id="removePremiacao-' . $row->id . '" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="' . route('premiacoes.delete', ['premiacao' => $row->id]) . '" method="POST">
                                @method(\'DELETE\')
                                @csrf
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Excluir Premiação</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        Tem certeza que deseja excluir a premiação?
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
                return '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#removePremiacao-' . $row->id . '">Remover</button>' . $modal;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AdivinhacoesPremiacoes $model): QueryBuilder
    {
        return $model->newQuery()
            ->select('adivinhacoes_premiacoes.id', 'adivinhacoes_premiacoes.created_at', 'users.name', 'users.username', 'adivinhacoes.titulo')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderBy('adivinhacoes_premiacoes.id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('premiacoesTable')
                    ->columns($this->getColumns())
                    ->ajax(route('dashboard.premiacoes.data'))
                    ->orderBy(0, 'desc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
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
            Column::make('username')->title('Usuário'),
            Column::make('titulo')->title('Título'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Premiacoes_' . date('YmdHis');
    }
}
