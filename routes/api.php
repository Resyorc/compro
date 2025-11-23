<?php

use App\Http\Controllers\AiProfilingController;
use App\Http\Controllers\TamuController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/ai/profiling/{siswa}', [AiProfilingController::class, 'show'])
        ->name('api.ai-profiling.show');
});

Route::apiResource('tamu_tabel', TamuController::class);
