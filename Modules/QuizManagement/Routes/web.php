<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\QuizManagement\Http\Controllers\QuizCategoriesController;
use Modules\QuizManagement\Http\Controllers\QuizParticipantesController;
use Modules\QuizManagement\Http\Controllers\QuizQuestionsController;
use Modules\QuizManagement\Http\Controllers\QuizResultsController;
use Modules\QuizManagement\Http\Controllers\QuizzesController;

Route::group(['middleware' => ['web', 'auth', 'verified', 'PlanModuleCheck:QuizManagement']], function () {
    Route::prefix('quizmanagement')->group(function () {
        // dashboard
        Route::get('dashboard/quiz', [QuizzesController::class, 'dashboard'])->name('dashboard.quiz');
        Route::resource('quizzes', QuizzesController::class)->names('quizzes');
        Route::resource('quiz-categories', QuizCategoriesController::class)->names('quiz.categories');
        Route::resource('quiz-questions', QuizQuestionsController::class)->names('quiz.questions');
        Route::resource('quiz-participants', QuizParticipantesController::class)->names('quiz.participate');
        Route::resource('quiz-results', QuizResultsController::class)->names('quiz.results');
    });
});

Route::get('quizmanagement/ajax/questions-by-category/{id}', [QuizzesController::class, 'getQuestionsByCategory']);


Route::middleware(['web'])->group(function () {
    // Route::get('/quizzes/{slug}/{lang?}', [QuizzesController::class, 'copylink'])->name('quizzes.participate.show');
    Route::get('/{slug}/quiz/{quiz_id}/{lang?}', [QuizzesController::class, 'copylink'])->name('quizzes.participate.show');
    Route::post('quiz-participants', [QuizParticipantesController::class, 'store'])->name('quiz.participate.store');
    Route::post('quiz-results', [QuizResultsController::class, 'store'])->name('quiz.results.store');
    Route::get('quiz-change-language-store/{slug?}/{lang}', [QuizzesController::class, 'changeLanquageStore'])->name('quiz.change.languagestore');
});
