<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\ServiceController;

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

Route::group(['middleware' => 'auth:sanctum'], function(){
    //All secure API's

    ////////// User section ////////////
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/editProfile', [AuthController::class, 'editProfile']);
    Route::put('/user/update/deviceToken', [UserController::class, 'updateDeviceToken']);
    Route::put('/user/updatePushNotificationStatus', [UserController::class, 'updatePushNotificationStatus']);
    Route::post('/user/getAllUsers', [UserController::class, 'getAllUsers']);
    Route::get('/user/getUserDataById', [UserController::class, 'getUserDataById']);
    Route::post('/user/getFollowers', [UserController::class, 'getFollowers']);
    Route::post('/user/getFollowing', [UserController::class, 'getFollowing']);
    Route::post('/user/getPendingRequests', [UserController::class, 'getPendingRequests']);
    Route::post('/user/acceptFollowRequest', [UserController::class, 'acceptFollowRequest']);
    Route::post('/user/changeBlockStatus', [UserController::class, 'changeBlockStatus']);
    Route::get('/user/getBlockedUsers', [UserController::class, 'getBlockedUsers']);
    Route::post('/user/followUser', [UserController::class, 'followUser']);
    Route::post('/user/unFollowUser', [UserController::class, 'unFollowUser']);
    Route::post('/user/deletePendingRequest', [UserController::class, 'deletePendingRequest']);
    Route::get('/user/getUserNotifications', [UserController::class, 'getUserNotifications']);
    Route::put('/user/notification/markAsRead', [UserController::class, 'updateNotificationStatus']);
    

    ////////// Posts Section ///////////////////////
    Route::post('/user/createPost', [PostController::class, 'createPost']);
    Route::post('getAllPosts', [PostController::class, 'getAllPosts']);
    Route::post('getFeedPosts', [PostController::class, 'getFeedPosts']);
    Route::post('getUserPosts', [PostController::class, 'getUserPosts']);
    Route::put('post/likePost', [PostController::class, 'likePost']);
    Route::put('post/comment', [PostController::class, 'commentPost']);
    Route::get('post/{postId}/viewComments', [PostController::class, 'viewPostComments']);


    ////////// Story Section ///////////////////////
    Route::post('/user/createStory', [StoryController::class, 'createStory']);
    Route::get('/user/getFeedStories', [StoryController::class, 'getFeedStories']);
    Route::get('/user/stories', [StoryController::class, 'getUserStories']);

    ///////// Services Section //////////////////////////////
    Route::post('services/insertServices', [ServiceController::class, 'insertServices']);
    Route::get('services/getUserServices', [ServiceController::class, 'getUserServices']);
    Route::post('services/createBooking', [ServiceController::class, 'createBooking']);
    Route::get('bookings/user/{user_id}', [ServiceController::class, 'getBookingsByUserId']);
});


/////////// Authentication //////////////////////////////
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

