<?php

namespace Modules\QuizManagement\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\Entities\QuizQuestions;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class QuizQuestionsDatatable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rowcolumn = ['type', 'options', 'category'];

        $dataTable = (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('type', function (QuizQuestions $quizQuestion) {
                if ($quizQuestion->type == 'true_false') {
                    return "True/False";
                } else if ($quizQuestion->type == 'mcq') {
                    return "MCQ";
                } else {
                    return "Descriptive";
                }
            })
            ->editColumn('options', function (QuizQuestions $quizQuestion) {
                $options = json_decode($quizQuestion->options, true);
                if (is_array($options) && isset($options['type']) && isset($options['values'])) {
                    if ($options['type'] === 'mcq' && is_array($options['values'])) {
                        return collect($options['values'])
                            ->map(function ($value, $key) {
                                return "<strong>{$key}:</strong> " . e($value);
                            })
                            ->implode('<br>');
                    } elseif ($options['type'] === 'true_false' && is_array($options['values'])) {
                        return implode(', ', array_map('e', $options['values']));
                    }
                }

                return '-';
            })
            ->editColumn('category', function (QuizQuestions $quizQuestion) {
                return $quizQuestion->category->name ?? 'Uncategorized';
            });

        if (\Laratrust::hasPermission('quizquestions edit') || \Laratrust::hasPermission('quizquestions delete')) {
            $dataTable->addColumn('action', function (QuizQuestions $quizQuestion) {
                return view('quiz-management::quiz-questions.action', compact('quizQuestion'));
            });

            $rowcolumn[] = 'action';
        }

        return $dataTable->rawColumns(array_merge($rowcolumn, ['action']));
    }

    public function query(QuizQuestions $model): QueryBuilder
    {
        return $model->newQuery()
            ->with('category')
            ->where('created_by', creatorId())
            ->where('workspace', getActiveWorkSpace());
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('quiz-questions-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0);
    }

    public function getColumns(): array
    {
        $column = [
            Column::make('id')->searchable(false)->visible(false)->exportable(false)->printable(false),
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('question')->title(__('Question')),
            Column::make('type')->title(__('Type')),
            Column::make('options')->title(__('Options')),
            Column::make('category')->title(__('Category')),
            Column::make('correct_answer')->title(__('Correct Answer')),
            Column::make('marks')->title(__('Marks')),
        ];

        if (\Laratrust::hasPermission('quizquestions edit') || \Laratrust::hasPermission('quizquestions delete')) {
            $action = [
                Column::computed('action')
                    ->exportable(false)
                    ->printable(false)
                    ->width(60)
                    ->addClass('text-center')
            ];
            $column = array_merge($column, $action);
        }

        return $column;
    }

    protected function filename(): string
    {
        return 'QuizQuestions_' . date('YmdHis');
    }
}
