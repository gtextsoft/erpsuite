<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Modules\ProductService\Http\Controllers\InvestmentController;
use Modules\ProductService\Http\Controllers\GtexthomesClientController;



Route::middleware('auth:api')->get('/productservice', function (Request $request) {
    return $request->user();
});


Route::prefix('api/productservice')->group(function () {
    Route::get('/investments', [InvestmentController::class, 'index'])->name('productservice.api.investments.index');
    Route::post('/investments/import', [InvestmentController::class, 'import'])->name('productservice.api.investments.import');
    Route::post('/gtextHomesclients/import', [GtexthomesClientController::class, 'import'])->name('clients.import');
});