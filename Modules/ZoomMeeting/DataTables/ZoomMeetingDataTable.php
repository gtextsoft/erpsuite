<?php

namespace Modules\ZoomMeeting\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\ZoomMeeting\Entities\ZoomMeeting;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ZoomMeetingDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowColumn = ['invitees', 'url', 'duration', 'status'];
        $dataTable = (new EloquentDataTable($query))
        ->addIndexColumn()
        ->editColumn('invitees',function(ZoomMeeting $zoomMeeting){
                if (!empty($zoomMeeting->users)){
                    $html = '';
                    foreach ($zoomMeeting->users as $user) {
                        $avatarSrc = $user->avatar ? get_file($user->avatar) : get_file('avatar.png');
                        $html .= '<img alt="image" data-bs-toggle="tooltip" data-bs-placement="top"
                            title="'. $user->name .'"
                            src="'. $avatarSrc .'"
                            class="rounded-circle" width="25" height="25">';
                    }
                }
                return $html;
        })
        ->editColumn('url',function(ZoomMeeting $zoomMeeting){
            $html = '';
            if (!empty($zoomMeeting->users)) {
                if (Auth::user()->id == $zoomMeeting->created_by) {
                    $html .= '<a target="_blank" href="' . ($zoomMeeting->start_url) . '" class="d-block mb-1">
                            <span class="d-inline-block">' . (__('Start URL')) . '</span>
                            <i class="ti ti-external-link" data-feather="external-link" style="font-size: 1.3rem;"></i>
                        </a>';

                }
                $html .= '<a target="_blank" href="'. $zoomMeeting->join_url .'" class="d-block mb-1">
                            <span class="pl-3">' . __('Join URL') . '</span>
                            <i class="ti ti-external-link" data-feather="external-link" style="font-size: 1.3rem;"></i>
                        </a>';
            }
            return $html;
        })
        ->editColumn('duration',function(ZoomMeeting $zoomMeeting){
            return $zoomMeeting->duration. __(' minute');
        })
        ->filterColumn('duration', function ($query, $keyword) {
            // $query->whereHas('vender', function ($q) use ($keyword) {
                $query->where('duration', 'like', "%$keyword%");
            // });
        })
        ->editColumn('status',function(ZoomMeeting $zoomMeeting){
            if ($zoomMeeting->status == 'waiting') {
                $html = '<span class="badge fix_badges bg-warning p-2 px-3">'. __('Waiting') .'</span>';
            } elseif ($zoomMeeting->status == 'start') {
                $html = '<span class="badge fix_badges bg-success p-2 px-3">'. __('Start') .'</span>';
            } else {
                $html = '<span class="badge fix_badges bg-danger p-2 px-3">'. __($zoomMeeting->status) .'</span>';
            }
            return $html;
        });
        if (\Laratrust::hasPermission('zoommeeting show') ||
        \Laratrust::hasPermission('zoommeeting delete'))
        {
            $dataTable->addColumn('action',function($zoomMeeting){
                return view('zoom-meeting::action',compact('zoomMeeting'));
            });
            $rowColumn[] = 'action';
        }
        return $dataTable->rawColumns($rowColumn);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ZoomMeeting $model): QueryBuilder
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'super admin') {
            $meetings = $model->select('zoom_meeting.*')
            ->join('general_meeting', 'zoom_meeting.id', '=', 'general_meeting.m_id')
            ->where('zoom_meeting.workspace_id', '=', getActiveWorkSpace()) // Add any additional conditions if needed
            ->with('users')
            ->groupBy('id');
        } else {
            $meetings = $model->select('zoom_meeting.*')
            ->join('general_meeting', 'zoom_meeting.id', '=', 'general_meeting.m_id')->where('general_meeting.user_id', \Auth::user()->id)
            ->where('zoom_meeting.workspace_id', '=', getActiveWorkSpace()) // Add any additional conditions if needed
            ->with('users')
            ->groupBy('id');
        }

        return $meetings;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        $dataTable = $this->builder()
            ->setTableId('zoom-meeting-table')
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
            Column::make('title')->title(__('Title')),
            Column::make('invitees')->title(__('Invitees'))->searchable(false)->exportable(false)->orderable(false),
            Column::make('start_date')->title(__('Meeting Date/Time')),
            Column::make('duration')->title(__('Duration')),
            Column::make('url')->title(__('URL'))->searchable(false)->orderable(false),
            Column::make('status')->title(__('Status')),
        ];

        if (\Laratrust::hasPermission('zoommeeting show') ||
        \Laratrust::hasPermission('zoommeeting delete'))
        {
            $action = [
                Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)

            ];

            $column = array_merge($column,$action);
        }

        return $column;

    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
