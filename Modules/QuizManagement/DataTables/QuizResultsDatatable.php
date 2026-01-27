<?php

namespace Modules\QuizManagement\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\Entities\QuizResults;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class QuizResultsDatatable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowcolumn = ['participant_id', 'quiz_id','status', 'total_time_teken', 'attempt_date'];

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('quiz_id', function (QuizResults $quizResults) {
                return optional($quizResults->quizzes)->name ?? '-';
            })
            ->editColumn('participant_id', function (QuizResults $quizResults) {
                return optional($quizResults->participants)->name ?? '-';
            })
            ->editColumn('status', function (QuizResults $quizResults) {
                if ($quizResults->status == 'Failed') {
                    $html = '<span class="badge fix_badges bg-danger p-2 px-3">Failed</span>';
                } else {
                    $html = '<span class="badge fix_badges bg-success p-2 px-3">Passed</span>';
                }

                return $html;
            })
            ->editColumn('total_time_teken', function (QuizResults $quizResults) {
                return $quizResults->total_time_teken . ' ' . __('Seconds');
            })
            ->editColumn('attempt_date', function (QuizResults $quizResults) {
                return $quizResults->attempt_date ? company_date_formate($quizResults->attempt_date ?? '-') : '-';
            });

        if (\Laratrust::hasPermission('quizresults edit') || \Laratrust::hasPermission('quizresults delete')) {
            $dataTable->addColumn('action', function (QuizResults $quizResults) {
                return view('quiz-management::quiz-results.action', compact('quizResults'));
            });

            $rowcolumn[] = 'action';
        }
        return $dataTable->rawColumns(array_merge($rowcolumn, ['action']));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(QuizResults $model): QueryBuilder
    {
        return $model->newQuery()
        ->where('quiz_results.created_by', creatorId())
        ->where('quiz_results.workspace', getActiveWorkSpace())
        ->leftJoin('quizzes', 'quiz_results.quiz_id', '=', 'quizzes.id')
        ->leftJoin('quiz_participants', 'quiz_results.participant_id', '=', 'quiz_participants.id')
        ->select([
            'quiz_results.*',
             'quiz_results.attempt_date as attempt_date',
             'quizzes.name as quiz_name',
             'quiz_participants.name as participants_name'
        ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('quiz-results-table')
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
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'csv',
                    'text' => '<i class="fas fa-file-csv me-2"></i> ' . __('CSV'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
                ],
                [
                    'extend' => 'excel',
                    'text' => '<i class="fas fa-file-excel me-2"></i> ' . __('Excel'),
                    'className' => 'btn btn-light text-primary dropdown-item',
                    'exportOptions' => ['columns' => [0, 1, 3]],
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
            // Column::make('participant_id')->title(__('Participant Name')),
            Column::make('attquiz_participantsempt_date')
            ->title(__('Participant Name'))
            ->data('participants_name')
            ->name('quiz_participants.name')
            ->searchable(true),
            Column::make('quiz_id')
                ->title(__('Quiz'))
                ->data('quiz_name')
                ->name('quizzes.name')
                ->searchable(true),
            Column::make('score')->title(__('Score')),
            Column::make('start_time')->title(__('Start Time')),
            Column::make('end_time')->title(__('End Time')),
            Column::make('status')->title(__('Status')),
            Column::make('total_time_teken')->title(__('Total Time Teken')),
            Column::make('attempt_date')
                ->title(__('Attempt Date'))
                ->data('attempt_date')
                ->name('quiz_results.attempt_date')
                ->searchable(true),
        ];
        if (\Laratrust::hasPermission('quizresults edit') || \Laratrust::hasPermission('quizresults delete')) {
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
        return 'QuizResults_' . date('YmdHis');
    }
}
