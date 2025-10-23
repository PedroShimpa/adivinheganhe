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
use Illuminate\Support\Facades\Cache;

class OnlineUsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('time_online', function($row) {
                $onlineUsers = Cache::get('online_users', []);
                $lastActivity = $onlineUsers[$row->id] ?? null;
                if ($lastActivity) {
                    $minutes = floor((now()->timestamp - $lastActivity) / 60);
                    return $minutes . ' min atrás';
                }
                return 'N/A';
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $onlineUsers = Cache::get('online_users', []);
        $userIds = array_keys($onlineUsers);

        $query = $model->newQuery()
            ->whereIn('id', $userIds)
            ->where('banned', false)
            ->orderByRaw('FIELD(id, ' . implode(',', $userIds) . ')');

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('onlineUsersTable')
                    ->columns($this->getColumns())
                    ->ajax(route('dashboard.online_users.data'))
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
            Column::computed('time_online')->title('Tempo Online'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'OnlineUsers_' . date('YmdHis');
    }
}
