<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get("dashboard", [\App\Http\Controllers\DashboardController::class, 'index']);
    Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index']);
    Route::post('/transactions', [\App\Http\Controllers\TransactionController::class, 'store']);
    Route::get('/transactions/{id}', [\App\Http\Controllers\TransactionController::class, 'show']);
    Route::get('/transactions-export-pdf', [\App\Http\Controllers\TransactionController::class, 'exportPdf']);
    Route::put('/transactions/{id}', [\App\Http\Controllers\TransactionController::class, 'update']);
    Route::delete('/transactions/{id}', [\App\Http\Controllers\TransactionController::class, 'destroy']);
    /**Users Management */
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [\App\Http\Controllers\UserController::class, 'store']);
    Route::get('/users/{id}', [\App\Http\Controllers\UserController::class, 'show']);
    Route::put('/users/{id}', [\App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);

    /**Form fields dinamis */
    Route::post('/form-fields', [\App\Http\Controllers\FormFieldController::class, 'store']);
    Route::put('/form-fields/{id}', [\App\Http\Controllers\FormFieldController::class, 'update']);
    Route::delete('/form-fields/{id}', [\App\Http\Controllers\FormFieldController::class, 'destroy']);
});
Route::get('/form-fields', [\App\Http\Controllers\FormFieldController::class, 'index']);
