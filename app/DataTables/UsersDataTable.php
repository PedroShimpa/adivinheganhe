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
            ->addColumn('created_at_formatted', function($row) {
                return $row->created_at->format('d/m/Y H:i:s');
            })
            ->addColumn('indicado_por', function($row) {
                if ($row->indicated_by) {
                    $indicatedUser = User::where('uuid', $row->indicated_by)->first();
                    return $indicatedUser ? $indicatedUser->name . ' (' . $indicatedUser->username . ')' : 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('action', function($row) {
                $modal = '<div class="modal fade" id="banModal-' . $row->id . '" tabindex="-1" aria-labelledby="banModalLabel-' . $row->id . '" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="banModalLabel-' . $row->id . '">Banir Usuário</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <div id="modal-content-' . $row->id . '" class="text-center">
                                    <div class="spinner-border text-danger" role="status">
                                        <span class="visually-hidden">Carregando...</span>
                                    </div>
                                    <p>Carregando formulário...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
                return '<button class="btn btn-danger btn-sm ban-btn" data-id="' . $row->id . '">Banir</button>' . $modal;
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->where('banned', false)
            ->orderBy('id', 'desc');

        if (request('start_date') && request('end_date')) {
            $query->whereBetween('created_at', [request('start_date') . ' 00:00:00', request('end_date') . ' 23:59:59']);
        }

        return $query;
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
            Column::make('name')->title('Nome'),
            Column::make('username')->title('Username'),
            Column::make('email')->title('Email'),
            Column::make('whatsapp')->title('Whatsapp'),
            Column::computed('indicado_por')->title('Indicado por'),
            Column::make('created_at_formatted')->title('Criado em'),
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
