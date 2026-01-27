<?php

namespace Modules\QuizManagement\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\Entities\Quizzes;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use App\Models\WorkSpace;

class QuizzesDatatable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowcolumn = ['category_id', 'per_qus_time', 'total_time_duration', 'color', 'status', 'image'];

        // Fetch the active workspace
        $workspace = WorkSpace::where('id', getActiveWorkSpace())->first();

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('category_id', function (Quizzes $quizzes) {
                return optional($quizzes->category)->name ?? '-';
            })
            ->editColumn('per_qus_time', function (Quizzes $quizzes) {
                return $quizzes->per_qus_time . ' ' . __('Seconds');
            })
            ->editColumn('total_time_duration', function (Quizzes $quizzes) {
                return $quizzes->total_time_duration . ' ' . __('Seconds');
            })
            ->editColumn('color', function (Quizzes $quizzes) {
                $color = $quizzes->color;
                if ($color == 'quiz-card-color-1') {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(126.29deg, #EF6F9C 1.72%, #EC8771 94.06%);"
                                data-value="quiz-card-color-1"></span>';
                } elseif ($color == 'quiz-card-color-2') {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(124.97deg, #467EEF -1.63%, #14B0F6 98.33%);"
                                data-value="quiz-card-color-2"></span>';
                } elseif ($color == 'quiz-card-color-3') {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(123.29deg, #477C4A -15.4%, #82E186 98.8%);"
                                data-value="quiz-card-color-3"></span>';
                } elseif ($color == 'quiz-card-color-4') {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(126.29deg, #7E75B9 1.72%, #BBB1FB 94.06%);"
                                data-value="quiz-card-color-4"></span>';
                } elseif ($color == 'quiz-card-color-5') {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(124.97deg, #C88F3A -1.63%, #F9C981 98.33%);"
                                data-value="quiz-card-color-5"></span>';
                } else {
                    $html = '
                    <span class="themes-color-change rounded-circle" style="background: linear-gradient(123.29deg, #C753B5 -15.4%, #F8AFED 98.8%);"
                                data-value="quiz-card-color-6"></span>';
                }
                return $html;
            })
            ->editColumn('status', function (Quizzes $quizzes) {
                return $quizzes->status == 1
                    ? '<span class="badge fix_badges bg-success p-2 px-3">Published</span>'
                    : '<span class="badge fix_badges bg-secondary p-2 px-3">Draft</span>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $keyword = strtolower($keyword);

                if (str_starts_with('published', $keyword)) {
                    $query->where('quizzes.status', 1);
                } elseif (str_starts_with('draft', $keyword)) {
                    $query->where('quizzes.status', 0);
                }
            })
            ->editColumn('image', function (Quizzes $quizzes) {
                if (check_file($quizzes->image) == false) {
                    $path = asset('Modules/ProductService/Resources/assets/image/img01.jpg');
                } else {
                    $path = get_file($quizzes->image);
                }
                return '<a href="' . $path . '" target="_blank">
                            <img src="' . $path . '" class="rounded border-2 border border-primary" style="width:100px;" id="blah3">
                        </a>';
            });

        if (\Laratrust::hasPermission('quizzes edit') || \Laratrust::hasPermission('quizzes show') || \Laratrust::hasPermission('quizzes delete')) {
            $dataTable->addColumn('action', function (Quizzes $quizzes) use ($workspace) {
                return view('quiz-management::quizzes.action', compact('quizzes', 'workspace'));
            });

            $rowcolumn[] = 'action';
        }

        return $dataTable->rawColumns(array_merge($rowcolumn, ['quiz_question', 'action']));
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Quizzes $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('quizzes.created_by', creatorId())
            ->where('quizzes.workspace', getActiveWorkSpace())
            ->with(['category', 'questions'])
            ->leftJoin('categories', 'quizzes.category_id', '=', 'categories.id')
            ->select([
                'quizzes.*',
                'categories.name as category_name'
            ]);
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('quizzes-table')
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
            Column::make('image')->title(__('Image')),
            Column::make('name')->title(__('Name')),
            Column::make('category_id')
                ->title(__('Category'))
                ->data('category_name')
                ->name('categories.name')
                ->searchable(true),
            Column::make('total_marks')->title(__('Total Marks')),
            Column::make('passing_marks')->title(__('Passing Marks')),
            Column::make('per_qus_time')->title(__('Per Questions Time')),
            Column::make('total_time_duration')->title(__('Total Time Duration')),
            Column::make('color')->title(__('Color')),
            Column::make('status')->title(__('Status')),
        ];
        if (\Laratrust::hasPermission('quizzes edit') || \Laratrust::hasPermission('quizzes show') || \Laratrust::hasPermission('quizzes delete')) {
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
        return 'Quizzes_' . date('YmdHis');
    }
}