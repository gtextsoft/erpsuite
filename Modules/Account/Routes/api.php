<?php

use Illuminate\Http\Request;
use Modules\Account\Http\Controllers\CustomerController;

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

Route::middleware('auth:api')->get('/account', function (Request $request) {
    return $request->user();
});

Route::post('/customers/bulk-update', [CustomerController::class, 'bulkCreate'])->middleware('jwt.api.auth');
// Route::post('/customers/bulk-update', [CustomerController::class, 'bulkUpdate'])->middleware('jwt.api.auth');
Route::post('customers/bulk-update-address', [CustomerController::class, 'bulkUpdateCustomerAddress'])->middleware('jwt.api.auth');