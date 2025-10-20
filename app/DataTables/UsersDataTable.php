<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function($row) {
                $modal = '<div class="modal fade" id="banModal-' . $row->id . '" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form action="' . route('user.ban', ['user' => $row->username]) . '" method="POST">
                                @csrf
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Banir Usuário</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                </div>
                                <div class="modal-body">
                                    <p>
                                        Tem certeza que deseja banir o usuário
                                        <strong>' . $row->name . ' (' . $row->username . ')</strong>?
                                    </p>
                                    <div class="mb-3">
                                        <label for="motivo-' . $row->id . '" class="form-label">Motivo (opcional)</label>
                                        <textarea name="motivo" id="motivo-' . $row->id . '" class="form-control" rows="3" placeholder="Escreva o motivo do banimento..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Banimento</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>';
                return '<button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#banModal-' . $row->id . '">Banir</button>' . $modal;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('banned', false)
            ->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('usersTable')
                    ->columns($this->getColumns())
                    ->ajax(route('dashboard.users.data'))
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
            Column::make('id')->title('ID'),
            Column::make('name')->title('Nome'),
            Column::make('username')->title('Username'),
            Column::make('email')->title('Email'),
            Column::make('whatsapp')->title('Whatsapp'),
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
        return 'Users_' . date('YmdHis');
    }
}
