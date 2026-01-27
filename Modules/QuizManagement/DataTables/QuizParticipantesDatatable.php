<?php

namespace Modules\QuizManagement\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\Entities\QuizParticipants;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class QuizParticipantesDatatable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowcolumn = ['quiz_id', 'status'];

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('quiz_id', function (QuizParticipants $quizParticipants) {
                return optional($quizParticipants->quizzes)->name ?? '-';
            })
            ->editColumn('status', function (QuizParticipants $quizParticipants) {
                if ($quizParticipants->status == 0) {
                    $html = '<span class="badge fix_badges bg-secondary p-2 px-3">Not Started</span>';
                } else {
                    $html = '<span class="badge fix_badges bg-success p-2 px-3">Completed</span>';
                }
                return $html;
            })
            ->editColumn('department', function (QuizParticipants $quizParticipants) {
                return $quizParticipants->department ?? '-';
            })
            ->editColumn('years_at_gtext', function (QuizParticipants $quizParticipants) {
                return $quizParticipants->years_at_gtext ?? '-';
            })
            ->editColumn('subsidiary', function (QuizParticipants $quizParticipants) {
                return $quizParticipants->subsidiary ?? '-';
            });

        if (\Laratrust::hasPermission('quizparticipants delete')) {
            $dataTable->addColumn('action', function (QuizParticipants $quizParticipants) {
                return view('quiz-management::quiz-participantes.action', compact('quizParticipants'));
            });

            $rowcolumn[] = 'action';
        }
        return $dataTable->rawColumns(array_merge($rowcolumn, ['action']));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(QuizParticipants $model): QueryBuilder
    {
        return $model->newQuery()->where('quiz_participants.created_by', creatorId())
            ->where('quiz_participants.workspace', getActiveWorkSpace())
            ->leftJoin('quizzes', 'quiz_participants.quiz_id', '=', 'quizzes.id')
            ->select([
                'quiz_participants.*',
                'quizzes.name as quiz_name'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('quiz-participantes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => "_MENU_" . __('Entries Per Page'),
                "searchPlaceholder" => __('Search...'),
                "search" => "",
                "info" => __('Showing _START_ to _END_ of _TOTAL_ entries')
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }');

        $exportButtonConfig = [
            'extend' => 'collection',
            'className' => 'btn btn-light-secondary dropdown-toggle',
            'text' => '<i class="ti ti-download me-2" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Export"></i>',
            'buttons' => [
                [
                    'extend' => 'print',
                    'text' => '<i class="fas fa-print me-2"></i> ' . __('Print'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3, 4, 5, 6, 7, 8]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3, 4, 5, 6, 7, 8]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3, 4, 5, 6, 7, 8]],
                ],
            ],
        ];

        $buttonsConfig = array_merge([
            $exportButtonConfig,
            [
                'extend' => 'reset',
                'className' => 'btn btn-light-danger',
            ],
            [
                'extend' => 'reload',
                'className' => 'btn btn-light-warning',
            ],
        ]);

        $dataTable->parameters([
            "dom" =>  "
        <'dataTable-top'<'dataTable-dropdown page-dropdown'l><'dataTable-botton table-btn dataTable-search tb-search  d-flex justify-content-end gap-2'Bf>>
        <'dataTable-container'<'col-sm-12'tr>>
        <'dataTable-bottom row'<'col-5'i><'col-7'p>>",
            'buttons' => $buttonsConfig,
            "drawCallback" => 'function( settings ) {
                var tooltipTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=tooltip]")
                  );
                  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                  });
                  var popoverTriggerList = [].slice.call(
                    document.querySelectorAll("[data-bs-toggle=popover]")
                  );
                  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                  });
                  var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                  var toastList = toastElList.map(function (toastEl) {
                    return new bootstrap.Toast(toastEl);
                  });
            }'
        ]);

        $dataTable->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print' => __('Print'),
                'reset' => __('Reset'),
                'reload' => __('Reload'),
                'excel' => __('Excel'),
                'csv' => __('CSV'),
            ]
        ]);

        return $dataTable;
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('email')->title(__('Email')),
            Column::make('mobile_no')->title(__('Mobile Number')),
            Column::make('department')->title(__('Department')),
            Column::make('years_at_gtext')->title(__('Years at Gtext')),
            Column::make('subsidiary')->title(__('Subsidiary')),
            Column::make('quiz_id')
                ->title(__('Quiz Name'))
                ->data('quiz_name')
                ->name('quizzes.name')
                ->searchable(true),
            Column::make('start_time')->title(__('Start Time')),
            Column::make('end_time')->title(__('End Time')),
            Column::make('status')->title(__('Status')),
        ];
        if (\Laratrust::hasPermission('quizparticipants delete')) {
            $action = [
                Column::computed('action')
                    ->exportable(false)
                    ->printable(false)
                    ->width(60)
            ];
            $column = array_merge($column, $action);
        }
        return $column;
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'QuizParticipantes_' . date('YmdHis');
    }
}