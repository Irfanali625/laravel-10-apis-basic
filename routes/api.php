<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\UserController;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/user/get/{flag}', [ApiController::class, 'index']);
Route::post('/user/store', [ApiController::class, 'store']);
Route::get('/user/{id}', [ApiController::class, 'show']);
Route::put('/user/update/{id}', [ApiController::class, 'update']);
Route::patch('/user/change-passwrod/{id}', [ApiController::class, 'changePassword']);
Route::delete('/user/delete/{id}', [ApiController::class, 'destroy']);


Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/user/{id}', [UserController::class, 'getUser']);
    Route::post('/logout', [UserController::class, 'logout']);
});
