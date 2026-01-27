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
use Modules\ToDoReport\Http\Controllers\ReportController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/todo-report', [ReportController::class, 'index'])->name('todo.report.index');
    Route::post('/todo-report/generate', [ReportController::class, 'generate'])->name('todo.report.generate');
});

