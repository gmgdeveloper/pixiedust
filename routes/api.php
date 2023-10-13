<?php

use App\Http\Controllers\Admin\AdminLoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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








Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/forgetPassword' , [AdminLoginController::class,'forgetPassword']); 

/////////// Authentication ///////////////////
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('/user/{userId}/profile', [AuthController::class, 'updateProfile']);
Route::put('/user/{userId}/editProfile', [AuthController::class, 'editProfile']);

////////// Followers/Users, following section ////////////
Route::post('/user/{userId}/getFollowers', [UserController::class, 'getFollowers']);
Route::post('/user/{userId}/getFollowing', [UserController::class, 'getFollowing']);
Route::post('/user/{userId}/getPendingRequests', [UserController::class, 'getPendingRequests']);
Route::post('/user/{userId}/acceptFollowRequest', [UserController::class, 'acceptFollowRequest']);
Route::post('/user/{userId}/deletePendingRequest', [UserController::class, 'deletePendingRequest']);
Route::post('/user/{userId}/blockUser', [UserController::class, 'blockUser']);
Route::post('/user/{userId}/unblockUser', [UserController::class, 'unblockUser']);
Route::post('/user/{userId}/getAllUsers', [UserController::class, 'getAllUsers']);
Route::post('/user/{userId}/createPost', [UserController::class, 'createPost']);

// Route::put('/user/{userId}/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
