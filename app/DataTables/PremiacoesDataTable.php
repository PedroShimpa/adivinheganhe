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
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') : '';
            })
            ->addColumn('pago', function ($row) {
                return $row->premio_enviado == 'S' ? 'Sim' : 'Não';
            })
            ->addColumn('action', function ($row) {
                $buttons = '';

                if ($row->premio_enviado == 'N') {
                    $buttons .= '<button class="btn btn-success btn-sm me-1 marcar-pago-btn" data-id="' . $row->id . '">Marcar Pago</button>';
                }

                $buttons .= '<button class="btn btn-danger btn-sm deletar-btn" data-id="' . $row->id . '">Remover</button>';

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

                $pagoModal = '<div class="modal fade" id="marcarPago-' . $row->id . '" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="' . route('premiacoes.marcar_pago', ['premiacao' => $row->id]) . '" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method(\'POST\')
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Marcar como Pago</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="comprovante" class="form-label">Comprovante de Pagamento (opcional)</label>
                                        <input type="file" class="form-control" id="comprovante" name="comprovante" accept="image/*">
                                        <small class="form-text text-muted">Aceita imagens: JPEG, PNG, JPG, GIF, SVG (máx. 2MB)</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Marcar como Pago</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';

                return $buttons . $modal . $pagoModal;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(AdivinhacoesPremiacoes $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->select('adivinhacoes_premiacoes.id', 'adivinhacoes_premiacoes.created_at', 'users.name', 'users.username', 'adivinhacoes.titulo', 'premio_enviado')
            ->join('adivinhacoes', 'adivinhacoes.id', '=', 'adivinhacoes_premiacoes.adivinhacao_id')
            ->join('users', 'users.id', '=', 'adivinhacoes_premiacoes.user_id')
            ->orderBy('adivinhacoes_premiacoes.id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('adivinhacoes_premiacoes.created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
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
            Column::computed('pago')->title('Pago'),
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
