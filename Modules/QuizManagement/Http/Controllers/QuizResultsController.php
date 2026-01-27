<?php

namespace Modules\QuizManagement\Http\Controllers;

use App\Models\WorkSpace;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\DataTables\QuizResultsDatatable;
use Modules\QuizManagement\Entities\QuizParticipants;
use Modules\QuizManagement\Entities\QuizQuestions;
use Modules\QuizManagement\Entities\QuizResults;
use Modules\QuizManagement\Entities\Quizzes;
use Modules\QuizManagement\Events\CreateQuizResults;
use Modules\QuizManagement\Events\DestroyQuizResults;

class QuizResultsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(QuizResultsDatatable $dataTable)
    {
        if (\Auth::user()->isAbleTo('quizresults manage')) {
            return $dataTable->render('quiz-management::quiz-results.index');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('quiz-management::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'participant_id'        => 'required',
                'quiz_id'               => 'required',
                'start_time'            => 'required',
                'end_time'              => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'is_success'    => false,
                    'error'         => $validator->errors()->first(),
                ],
                422
            );
        }

        $score = 0;
        $status = 'Failed';
        $questions = $request->correct_answers;

        if ($questions) {
            foreach ($questions as $key => $question) {
                $question = QuizQuestions::find($question);
                if ($question) {
                    $score += $question->marks;
                }
            }
        } else {
            $score  = 0;
        }

        $quiz  = Quizzes::find($request->quiz_id);
        if ($score >=  $quiz->passing_marks) {
            $status = 'Passed';
        }

        $quizResult                     = new QuizResults();
        $quizResult->participant_id     = $request->participant_id;
        $quizResult->quiz_id            = $request->quiz_id;
        $quizResult->score              = $score;
        $quizResult->start_time         = date('H:i:s', strtotime($request->start_time));
        $quizResult->end_time           = date('H:i:s', strtotime($request->end_time));
        $quizResult->status             = $status;
        $startTime                      = Carbon::parse($quizResult->start_time);
        $endTime                        = Carbon::parse($quizResult->end_time);
        $timeDifferenceInSeconds        = $startTime->diffInSeconds($endTime);
        $quizResult->total_time_teken   = $timeDifferenceInSeconds;
        $quizResult->attempt_date       = now()->toDateString();

        $quiz                           = Quizzes::find($request->quiz_id);
        if (!$quiz) {
            return response()->json([
                'is_success'    => false,
                'error'         => __('Quiz not found!'),
            ], 404);
        }

        $quizResult->created_by         = $quiz->created_by;
        $quizResult->workspace          = $request->workspace_id;
        $quizResult->save();

        $quizParticipants               = QuizParticipants::find($request->participant_id);
        $quizParticipants->start_time   = date('H:i:s', strtotime($request->start_time));
        $quizParticipants->end_time     = date('H:i:s', strtotime($request->end_time));
        $quizParticipants->status       = 1;

        $quizParticipants->save();

        event(new CreateQuizResults($request, $quizResult));

        $return = [
            'is_success'        => true,
            'participant_id'    => $quizParticipants->id,
            'workspace_id'      => $quizParticipants->workspace,
            'quiz'              => $quiz,
            'score'             => $score,
            'status'            => $status,
            'questions'         => [],
        ];
        return response()->json($return);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('quiz-management::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('quiz-management::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (\Auth::user()->isAbleTo('quizresults delete')) {
            $quizResult = QuizResults::find($id);

            if (!$quizResult) {
                return redirect()->back()->with('error', __('Quiz results not found.'));
            }

            QuizParticipants::where('id', $quizResult->participant_id)->delete();

            event(new DestroyQuizResults($quizResult));
            $quizResult->delete();

            return redirect()->back()->with('success', __('The quiz result has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
