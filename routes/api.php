<?php

use App\Http\Controllers\Api\AccessController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

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

Route::get('/laravel-version', function (Request $request) {
    $validToken = env('APPVERSION'); // Replace with your secure token

    if ($request->query('token') !== $validToken) {
        abort(403, 'Unauthorized access');
    }
    return response()->json([
        'laravel_version' => App::version(),
    ]);
});

// Panggil controller untuk ringkasan akses hari ini
Route::get('/today-access-summary', [AccessController::class, 'todayAccessSummary']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
