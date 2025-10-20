<?php

namespace App\DataTables;

use App\Models\Adivinhacoes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AdivinhacoesAtivasDataTable extends DataTable
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
            ->addColumn('respostas_count', function ($row) {
                return $row->respostas->count();
            })
            ->addColumn('action', function ($row) {
                $modal = '<div class="modal fade" id="removeAdivinhacao-' . $row->id . '" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="' . route('adivinhacoes.delete', ['adivinhacao' => $row->uuid]) . '" method="POST">
                                @method(\'DELETE\')
                                @csrf
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Excluir Adivinhação</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        Tem certeza que deseja excluir a adivinhação?
                                        <strong>' . $row->titulo . '</strong>?
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
                return '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#removeAdivinhacao-' . $row->id . '">Remover</button>' . $modal;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Adivinhacoes $model): QueryBuilder
    {
        return $model->newQuery()->whereNull('regiao_id')
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
            ->withCount('likes');;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('adivinhacoesAtivasTable')
            ->columns($this->getColumns())
            ->ajax(route('dashboard.adivinhacoes_ativas.data'))
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel')->text('<i class="fas fa-file-excel"></i> Excel'),
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
            Column::make('uuid')->title('Código'),
            Column::make('created_at')->title('Data de criação'),
            Column::make('titulo')->title('Título'),
            Column::computed('respostas_count')->title('Qtd Respostas'),
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
        return 'AdivinhacoesAtivas_' . date('YmdHis');
    }
}
