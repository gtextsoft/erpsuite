<?php

namespace Modules\QuizManagement\Http\Controllers;

use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Modules\QuizManagement\DataTables\QuizParticipantesDatatable;
use Modules\QuizManagement\Entities\QuizParticipants;
use Modules\QuizManagement\Entities\QuizResults;
use Modules\QuizManagement\Entities\Quizzes;
use Modules\QuizManagement\Events\CreateQuizParticipantes;
use Modules\QuizManagement\Events\DestroyQuizParticipantes;

class QuizParticipantesController extends Controller
{
    public function index(QuizParticipantesDatatable $dataTable)
    {
        if (\Auth::user()->isAbleTo('quizparticipants manage')) {
            return $dataTable->render('quiz-management::quiz-participantes.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        return view('quiz-management::create');
    }

    public function store(Request $request)
    {
        $quizParticipants = QuizParticipants::where('name', $request->name)
            ->where('email', $request->email)
            ->where('status', 0)
            ->first();
        
        if (!$quizParticipants) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'slug' => 'required|string|max:255',
                    'name' => 'required|string|max:255',
                    'email' => [
                        'required',
                        'email',
                        'max:100',
                        Rule::unique('quiz_participants')->where(function ($query) use ($request) {
                            return $query->where('quiz_id', $request->quiz_id);
                        }),
                    ],
                    'mobile_no' => 'required',
                    'department' => 'nullable|string|max:255',
                    'years_at_gtext' => 'nullable|string|max:255',
                    'subsidiary' => 'nullable|string|max:255',
                    'start_time' => 'nullable',
                    'end_time' => 'nullable',
                    'status' => 'nullable',
                ]
            );

            if ($validator->fails()) {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => $validator->errors()->first(),
                    ],
                    422
                );
            }

            $quizParticipants = new QuizParticipants();
            $quizParticipants->name = $request->name;
            $quizParticipants->email = $request->email;
            $quizParticipants->mobile_no = $request->mobile_no;
            $quizParticipants->department = $request->department;
            $quizParticipants->years_at_gtext = $request->years_at_gtext;
            $quizParticipants->subsidiary = $request->subsidiary;
            $quizParticipants->quiz_id = $request->quiz_id;
            $quizParticipants->status = 0;

            $workspace = WorkSpace::where('slug', $request->slug)->first();
            if (!$workspace) {
                return response()->json([
                    'is_success' => false,
                    'error' => __('Workspace not found!'),
                ], 404);
            }

            $quiz = Quizzes::find($request->quiz_id);
            if (!$quiz) {
                return response()->json([
                    'is_success' => false,
                    'error' => __('Quiz not found!'),
                ], 404);
            }

            $quizParticipants->created_by = $quiz->created_by;
            $quizParticipants->workspace = $workspace->id;
            $quizParticipants->save();
        }

        $quiz = Quizzes::with('questions')->find($request->quiz_id);

        if (!$quiz) {
            return response()->json([
                'is_success' => false,
                'error' => __('Quiz not found!'),
            ], 404);
        }

        $questions = $quiz->questions->toArray();
        $return = [
            'is_success' => true,
            'participant_id' => $quizParticipants->id,
            'workspace_id' => $quizParticipants->workspace,
            'quiz' => $quiz,
            'questions' => [],
        ];

        foreach ($questions as $question) {
            $return['questions'][] = [
                'id' => $question['id'],
                'question' => $question['question'],
                'options' => !empty($question['options']) ? json_decode($question['options'], true) : null,
                'correct_answer' => $question['correct_answer'],
                'marks' => $question['marks'],
            ];
        }

        event(new CreateQuizParticipantes($request, $quizParticipants));

        return response()->json($return);
    }

    public function show($id)
    {
        return view('quiz-management::show');
    }

    public function edit($id)
    {
        return view('quiz-management::edit');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        if (\Auth::user()->isAbleTo('quizparticipants delete')) {
            $quizParticipant = QuizParticipants::find($id);

            if (!$quizParticipant) {
                return redirect()->back()->with('error', __('quiz participant not found.'));
            }

            $quizResults = QuizResults::where('participant_id', $id)->get();

            if ($quizResults->count() > 0) {
                QuizResults::where('participant_id', $id)->delete();
            }
            event(new DestroyQuizParticipantes($quizParticipant));
            $quizParticipant->delete();

            return redirect()->back()->with('success', __('The quiz participant has been deleted'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}