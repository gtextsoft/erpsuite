<?php

namespace Modules\QuizManagement\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\QuizManagement\DataTables\QuizCategoriesDatatable;
use Modules\QuizManagement\Entities\QuizCategories;
use Modules\QuizManagement\Entities\Quizzes;
use Modules\QuizManagement\Events\CreateQuizCategories;
use Modules\QuizManagement\Events\DestroyQuizCategories;
use Modules\QuizManagement\Events\UpdateQuizCategories;

class QuizCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(QuizCategoriesDatatable $dataTable)
    {
        if (\Auth::user()->isAbleTo('quizcategories manage')) {
            return $dataTable->render('quiz-management::quiz-categories.index');
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
        if (\Auth::user()->isAbleTo('quizcategories create')) {
            return view('quiz-management::quiz-categories.create');
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
        if (\Auth::user()->isAbleTo('quizcategories create')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'        => 'required|string|max:255',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $quizCategorie              = new QuizCategories();
            $quizCategorie->name        = $request->name;
            $quizCategorie->workspace   = getActiveWorkSpace();
            $quizCategorie->created_by  = creatorId();
            $quizCategorie->save();
            event(new CreateQuizCategories($request, $quizCategorie));
            return redirect()->back()->with('success', __('The quiz categories has been created successfully.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
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
        if (\Auth::user()->isAbleTo('quizcategories edit')) {
            $quizCategorie  = QuizCategories::find($id);
            return view('quiz-management::quiz-categories.edit', compact('quizCategorie'));
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
        if (\Auth::user()->isAbleTo('quizcategories edit')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name'        => 'required|string|max:255',
                ]
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $quizCategorie              = QuizCategories::find($id);

            if (!$quizCategorie) {
                return redirect()->back()->with('error', __('Quiz category not found!'));
            }

            $quizCategorie->name        = $request->name;
            $quizCategorie->save();
            event(new UpdateQuizCategories($request, $quizCategorie));
            return redirect()->back()->with('success', __('The quiz categories details are updated successfully.'));
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
        if (\Auth::user()->isAbleTo('quizcategories delete')) {
            $quizCategorie = QuizCategories::find($id);

            if (!$quizCategorie) {
                return redirect()->back()->with('error', __('Quiz category not found!'));
            }

            $quizzes       = Quizzes::where('category_id', $id)->get();
            if ($quizzes->count() > 0) {
                return redirect()->back()->with('error', __('The quiz categories has already used in Quiz.'));
            }
            event(new DestroyQuizCategories($quizCategorie));
            $quizCategorie->delete();
            return redirect()->back()->with('success', __('The quiz categories has been deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
