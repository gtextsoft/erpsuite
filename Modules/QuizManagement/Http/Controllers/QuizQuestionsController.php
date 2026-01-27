<?php

namespace Modules\QuizManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\DataTables\QuizQuestionsDatatable;
use Modules\QuizManagement\Entities\QuizQuestions;
use Modules\QuizManagement\Entities\QuizzesQuestions;
use Modules\QuizManagement\Entities\QuestionCategory;
use Modules\QuizManagement\Events\CreateQuizQuestions;
use Modules\QuizManagement\Events\DestroyQuizQuestions;
use Modules\QuizManagement\Events\UpdateQuizQuestions;

class QuizQuestionsController extends Controller
{
    public function index(QuizQuestionsDatatable $dataTable)
    {
        if (Auth::user()->isAbleTo('quizquestions manage')) {
            $categories = QuestionCategory::pluck('name', 'id');
            return $dataTable->render('quiz-management::quiz-questions.index', compact('categories'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (Auth::user()->isAbleTo('quizquestions create')) {
            $categories = QuestionCategory::pluck('name', 'id');
            return view('quiz-management::quiz-questions.create', compact('categories'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->isAbleTo('quizquestions create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'question' => 'required|string|max:255',
                    'type' => 'required|string',
                    'mcq_values' => 'nullable|array',
                    'true_false_values' => 'nullable|array',
                    'correct_answer' => 'required|string',
                    'marks' => 'required|integer|min:1',
                    'category_id' => 'nullable|exists:question_categories,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $options = [];

            if ($request->type === 'mcq' && !empty($request->mcq_values)) {
                $filteredMcqValues = collect($request->mcq_values)
                    ->filter(fn($item) => isset($item['mcq_values']) && !empty($item['mcq_values']))
                    ->pluck('mcq_values')
                    ->values();

                $mcqOptions = [];
                foreach ($filteredMcqValues as $index => $value) {
                    $mcqOptions[chr(65 + $index)] = $value;
                }

                $options = ['type' => 'mcq', 'values' => $mcqOptions];
            } elseif ($request->type === 'true_false' && !empty($request->true_false_values)) {
                $options = [
                    'type' => 'true_false',
                    'values' => collect($request->true_false_values)
                        ->pluck('true_false_values')
                        ->filter()
                        ->values()
                        ->toArray()
                ];
            }

            $optionsJson = !empty($options) ? json_encode($options) : null;

            $quizQuestion = new QuizQuestions();
            $quizQuestion->question = $request->question;
            $quizQuestion->type = $request->type;
            $quizQuestion->options = $optionsJson;
            $quizQuestion->correct_answer = $request->correct_answer;
            $quizQuestion->marks = $request->marks;
            $quizQuestion->workspace = getActiveWorkSpace();
            $quizQuestion->created_by = creatorId();
            $quizQuestion->category_id = $request->category_id;
            $quizQuestion->save();

            event(new CreateQuizQuestions($request, $quizQuestion));
            return redirect()->back()->with('success', __('The question has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->isAbleTo('quizquestions edit')) {
            $quizQuestion = QuizQuestions::find($id);
            $categories = QuestionCategory::pluck('name', 'id');
            return view('quiz-management::quiz-questions.edit', compact('quizQuestion', 'categories'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->isAbleTo('quizquestions edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'question' => 'required|string|max:255',
                    'type' => 'required|string',
                    'mcq_values' => 'nullable|array',
                    'true_false_values' => 'nullable|array',
                    'correct_answer' => 'required|string',
                    'marks' => 'required|integer|min:1',
                    'category_id' => 'nullable|exists:question_categories,id',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $quizQuestion = QuizQuestions::find($id);
            if (!$quizQuestion) {
                return redirect()->back()->with('error', __('Question not found.'));
            }

            $options = [];

            if ($request->type === 'mcq' && !empty($request->mcq_values)) {
                $filteredMcqValues = collect($request->mcq_values)
                    ->filter(fn($item) => isset($item['mcq_values']) && !empty($item['mcq_values']))
                    ->pluck('mcq_values')
                    ->values();

                $mcqOptions = [];
                foreach ($filteredMcqValues as $index => $value) {
                    $mcqOptions[chr(65 + $index)] = $value;
                }

                $options = ['type' => 'mcq', 'values' => $mcqOptions];
            } elseif ($request->type === 'true_false' && !empty($request->true_false_values)) {
                $options = [
                    'type' => 'true_false',
                    'values' => collect($request->true_false_values)
                        ->pluck('true_false_values')
                        ->filter()
                        ->values()
                        ->toArray()
                ];
            }

            $optionsJson = !empty($options) ? json_encode($options) : null;

            $quizQuestion->question = $request->question;
            $quizQuestion->type = $request->type;
            $quizQuestion->options = $optionsJson;
            $quizQuestion->correct_answer = $request->correct_answer;
            $quizQuestion->marks = $request->marks;
            $quizQuestion->category_id = $request->category_id;
            $quizQuestion->save();

            event(new UpdateQuizQuestions($request, $quizQuestion));
            return redirect()->back()->with('success', __('The question details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->isAbleTo('quizquestions delete')) {
            $quizQuestion = QuizQuestions::find($id);
            if (!$quizQuestion) {
                return redirect()->back()->with('error', __('Quiz question not found!'));
            }

            $Question = QuizzesQuestions::where('question_id', $id)->get();

            if ($Question->count() > 0) {
                return redirect()->back()->with('error', __('The quiz question has already been used in a Quiz.'));
            }

            event(new DestroyQuizQuestions($quizQuestion));
            $quizQuestion->delete();
            return redirect()->back()->with('success', __('The question has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
