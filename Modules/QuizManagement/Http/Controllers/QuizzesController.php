<?php

namespace Modules\QuizManagement\Http\Controllers;

use App\Models\WorkSpace;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\DataTables\QuizzesDatatable;
use Modules\QuizManagement\Entities\QuizCategories;
use Modules\QuizManagement\Entities\QuizParticipants;
use Modules\QuizManagement\Entities\QuizQuestions;
use Modules\QuizManagement\Entities\QuizResults;
use Modules\QuizManagement\Entities\Quizzes;
use Modules\QuizManagement\Events\CreateQuizzes;
use Modules\QuizManagement\Events\DestroyQuizzes;
use Modules\QuizManagement\Events\QuizzesCreate;
use Modules\QuizManagement\Events\QuizzesDestroy;
use Modules\QuizManagement\Events\QuizzesUpdate;
use Modules\QuizManagement\Events\UpdateQuizzes;
use Modules\QuizManagement\Entities\QuestionCategory;


class QuizzesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(QuizzesDatatable $dataTable)
    {
        if (\Auth::user()->isAbleTo('quizzes manage')) {
            $workspace = WorkSpace::where('id', getActiveWorkSpace())->first();
            return $dataTable->render('quiz-management::quizzes.index', compact('workspace'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function dashboard()
    {
        if (\Auth::user()->isAbleTo('quiz dashboard manage')) {

            $workspace          = WorkSpace::where('id', getActiveWorkSpace())->first();
            $totalQuizzes       = Quizzes::where('workspace', $workspace->id)->where('created_by', creatorId())->count();
            $quizQuestions      = QuizQuestions::where('workspace', $workspace->id)->where('created_by', creatorId())->count();
            $quizParticipants   = QuizParticipants::where('workspace', $workspace->id)->where('created_by', creatorId())->count();
            $quizResults        = QuizResults::where('workspace', $workspace->id)->where('created_by', creatorId())->count();
            $resultsLast_5      = QuizResults::with(['participants', 'quizzes'])
                ->where('workspace', $workspace->id)
                ->where('created_by', creatorId())
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $m              = date("m");
            $d              = date("d");
            $y              = date("Y");
            $format         = 'Y-m-d';
            $arrDate        = [];
            $arrDateFormat  = [];
            $arrDay         = [];

            for ($i = 0; $i < 7; $i++) {
                $date               = date($format, mktime(0, 0, 0, $m, ($d - $i), $y));
                $arrDay[]           = date('D', mktime(0, 0, 0, $m, ($d - $i), $y));
                $arrDate[]          = $date;
                $arrDateFormat[]    = date("d-M", strtotime($date));
            }

            $datachartArr['day'] = $arrDateFormat;

            $quizArr = [];
            foreach ($arrDate as $date) {
                $quizzes            = Quizzes::where('workspace', $workspace->id)->where('created_by', creatorId())
                    ->whereDate('created_at', '=', $date)
                    ->count();
                $quizArr[]          = $quizzes;

                $Participants       = QuizParticipants::where('workspace', $workspace->id)->where('created_by', creatorId())->whereDate('created_at', '=', $date)->count();
                $participantsArr[]  = $Participants;

                $Passed             = QuizResults::where('workspace', $workspace->id)->where('status', 'Passed')->where('created_by', creatorId())->whereDate('created_at', '=', $date)->count();
                $passedArr[]        = $Passed;

                $Failed             = QuizResults::where('workspace', $workspace->id)->where('status', 'Failed')->where('created_by', creatorId())->whereDate('created_at', '=', $date)->count();
                $failedArr[]        = $Failed;
            }

            $datachartArr['quizzes']        = $quizArr;
            $datachartArr['participants']   = $participantsArr;
            $datachartArr['passed']         = $passedArr;
            $datachartArr['failed']         = $failedArr;

            return view('quiz-management::dashboard.dashboard', compact('workspace', 'totalQuizzes', 'quizQuestions', 'quizParticipants', 'quizResults', 'resultsLast_5', 'datachartArr'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
public function getQuestionsByCategory($categoryId)
{
    $questions = QuizQuestions::where('category_id', $categoryId)->get(['id', 'question', 'marks']);
    return response()->json($questions);
}



    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    // public function create()
    // {
    //     if (\Auth::user()->isAbleTo('quizzes create')) {
    //         $quizQuestions  = QuizQuestions::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
    //         $quizCategories = QuizCategories::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
    //         $minutes        = Quizzes::getMinutesRange();
    //         return view('quiz-management::quizzes.create', compact('quizQuestions', 'minutes', 'quizCategories'));
    //     } else {
    //         return response()->json(['error' => __('Permission denied.')], 401);
    //     }
    // }
    
    
public function create(Request $request)
{
    if (\Auth::user()->isAbleTo('quizzes create')) {
        $categories = QuestionCategory::all();

        $questions = QuizQuestions::with('category')
            ->where('workspace', getActiveWorkSpace())
            ->where('created_by', creatorId())
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->get();

        $quizCategories = QuizCategories::where('created_by', creatorId())
            ->where('workspace', getActiveWorkSpace())
            ->get()
            ->pluck('name', 'id');

        $minutes = Quizzes::getMinutesRange();

        return view('quiz-management::quizzes.create', compact(
            'questions',
            'categories',
            'quizCategories',
            'minutes'
        ));
    } else {
        return response()->json(['error' => __('Permission denied.')], 401);
    }
}


    
    

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (\Auth::user()->isAbleTo('quizzes create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'                => 'required|string|max:255',
                    'category'            => 'required',
                    'quiz_question'       => 'required|array',
                    'total_marks'         => 'required|numeric',
                    'passing_marks'       => 'required|numeric',
                    'per_qus_time'        => 'required|numeric',
                    'total_time_duration' => 'required|numeric',
                    'color'               => 'required|string',
                    'status'              => 'required',
                    'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            if ($request->total_marks < $request->passing_marks) {
                return redirect()->back()->with('error', __('The passing marks cannot be greater than the total marks.'));
            }

            $quiz                       = new Quizzes();
            $quiz->name                 = $request->name;
            $quiz->category_id          = $request->category;
            $quiz->total_marks          = $request->total_marks;
            $quiz->passing_marks        = $request->passing_marks;
            $quiz->per_qus_time         = $request->per_qus_time;
            $quiz->total_time_duration  = $request->total_time_duration;
            $quiz->color                = $request->color;
            $quiz->status               = $request->status;
            $quiz->workspace            = getActiveWorkSpace();
            $quiz->created_by           = creatorId();

            if ($request->hasFile('image')) {
                $name = time() . "_" . $request->image->getClientOriginalName();
                $path = upload_file($request, 'image', $name, 'quizzes');

                if ($path['flag'] == 1) {
                    $quiz->image = $path['url'];

                    $fullPath = public_path($path['url']);

                    if (file_exists($fullPath)) {
                        chmod($fullPath, 0777);
                    }
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            } else {
                $quiz->image = null;
            }


            $quiz->save();

            if ($request->has('quiz_question')) {
                $quiz->questions()->sync($request->quiz_question);
            }
            event(new CreateQuizzes($request, $quiz));
            return redirect()->back()->with('success', __('The quiz has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function changeLanquageStore($slug, $lang)
    {
        session(['lang' => $lang]);

        return redirect()->back()->with('success', __('The language has been changed successfully.'));
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (\Auth::user()->isAbleTo('quizzes show')) {
            $quizzes  = Quizzes::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('questions')->find($id);
            if (!$quizzes) {
                return redirect()->back()->with('error', __('Quiz questions not found!'));
            }

            $quizQuestions = $quizzes->questions;

            return view('quiz-management::quizzes.show', compact('quizQuestions'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (\Auth::user()->isAbleTo('quizzes edit')) {
            $quiz           = Quizzes::with('questions')->find($id);
            $quizQuestions  = QuizQuestions::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get();
            $quizCategories = QuizCategories::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $minutes        = Quizzes::getMinutesRange();
            return view('quiz-management::quizzes.edit', compact('quiz', 'quizQuestions', 'quizCategories', 'minutes'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (\Auth::user()->isAbleTo('quizzes edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'                => 'required|string|max:255',
                    'category'            => 'required',
                    'quiz_question'       => 'required|array',
                    'total_marks'         => 'required|numeric',
                    'passing_marks'       => 'required|numeric',
                    'per_qus_time'        => 'required|numeric',
                    'total_time_duration' => 'required|numeric',
                    'color'               => 'required|string',
                    'status'              => 'required',
                    'image'               => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            if ($request->total_marks < $request->passing_marks) {
                return redirect()->back()->with('error', __('The passing marks cannot be greater than the total marks.'));
            }

            $quiz                       = Quizzes::findOrFail($id);
            $quiz->name                 = $request->name;
            $quiz->category_id          = $request->category;
            $quiz->total_marks          = $request->total_marks;
            $quiz->passing_marks        = $request->passing_marks;
            $quiz->per_qus_time         = $request->per_qus_time;
            $quiz->total_time_duration  = $request->total_time_duration;
            $quiz->color                = $request->color;
            $quiz->status               = $request->status;

            if ($request->hasFile('image')) {
                if (!empty($quiz->image)) {
                    delete_file($quiz->image);
                }

                $name = time() . "_" . $request->image->getClientOriginalName();
                $path = upload_file($request, 'image', $name, 'quizzes');
                if ($path['flag'] == 1) {
                    $quiz->image          = empty($path) ? null : $path['url'];

                    $fullPath = public_path($path['url']);

                    if (file_exists($fullPath)) {
                        chmod($fullPath, 0777);
                    }
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }
            $quiz->save();

            if ($request->has('quiz_question')) {
                $quiz->questions()->sync($request->quiz_question);
            }
            event(new UpdateQuizzes($request, $quiz));
            return redirect()->back()->with('success', __('The quiz details are updated successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (\Auth::user()->isAbleTo('quizzes delete')) {
            $quiz = Quizzes::findOrFail($id);
            if (!$quiz) {
                return redirect()->back()->with('error', __('Quiz not found!'));
            }
            $quiz->questions()->detach();
            event(new DestroyQuizzes($quiz));
            $quiz->delete();
            return redirect()->back()->with('success', __('The quiz has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    
    public function copylink($slug, $quiz_id, $lang = null)
{
    $workspace = WorkSpace::where('slug', $slug)->first();
    if ($workspace) {
        // Fetch only the specific quiz by ID
        $quiz = Quizzes::with('questions')
            ->where('workspace', $workspace->id)
            ->where('id', $quiz_id)
            ->where('status', 1)
            ->first();

        if (!$quiz) {
            abort(404, __('Quiz not found.'));
        }

        $currantLang = session()->get('lang') ?? $lang;
        $languages = languages();
        \App::setLocale($currantLang);
        $lang = $currantLang;

        // Pass only the single quiz to the view
        return view('quiz-management::frontend.index', compact('workspace', 'slug', 'lang', 'quiz'));
    } else {
        abort(404, __('Workspace not found.'));
    }
}

    // public function copylink($slug, $lang = null)
    // {
    //     $workspace       = WorkSpace::where('slug', $slug)->first();
    //     if ($workspace) {
    //         $quizzes        = Quizzes::with('questions')->where('workspace', $workspace->id)->where('status', 1)->orderBy('id', 'DESC')->get();
    //         $currantLang    = session()->get('lang');
    //         $languages      = languages();
    //         \App::setLocale($currantLang);
    //         $lang = $currantLang;

    //         return view('quiz-management::frontend.index', compact('workspace', 'slug', 'lang', 'quizzes'));
    //     } else {
    //         abort(404);
    //     }
    // }
}
