<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\ServiceController;
use App\Models\Notifications;

//Get Users Update Delete and fetch /////////
Route::post("/create_user", [AdminController::class, 'create_user']);
Route::get("/get-all-users", [AdminController::class, 'get_all_users']);
Route::get('/get_user/{id}', [AdminController::class, 'get_user']);
Route::post('/edit_user/{id}', [AdminController::class, 'edit_user']);
Route::delete('/delete_user/{id}', [AdminController::class, 'delete_user']);
Route::get('/showAllRecordUser/{id}', [AdminController::class, 'showAllRecordUser']);



////////Admin Login /////////////
Route::post('/admin_create', [AdminLoginController::class, 'admin_create']);
Route::post('/login_admin', [AdminLoginController::class, 'login_admin']);
Route::get('/getAllAdmins' ,[AdminLoginController::class,'getAllAdmins']);
Route::post('/getAdminUpdate/{id}', [AdminLoginController::class,'getAdminUpdate']);


/////////// logout/////////////////
Route::post('/logout', [AdminLoginController::class, 'Logout']);



//////////Services/////////////////

Route::get('/services', [ServiceController::class, 'get_All_User_Post']);
Route::get('/get-post-id/{id}', [ServiceController::class, 'get_post_id']);
Route::post('/edit_user_post/{id}', [ServiceController::class, 'edit_user_post']);
Route::delete('/delete_post/{id}', [ServiceController::class, 'delete_post']);





//   Notifications ///
Route::get('/getNotifications', [ServiceController::class, 'getNotifications']);

////POSTS////
Route::get('/getAllPosts', [ServiceController::class, 'getAllPosts']);
Route::get('/getSinglePost/{id}', [ServiceController::class,'getSinglePost']);
Route::post('/UpdatePost/{id}', [ServiceController::class, 'UpdatePost']);
Route::delete('DeletePostData/{id}',[ServiceController::class, 'DeletePostData']);


///Comment//
Route::get('edit/{id}',[ServiceController::class, 'getSingleRecord']);
Route::post('editComments/{id}',[ServiceController::class, 'editComments']);
Route::delete('/deleteComment/{postId}/{commentId}', [ServiceController::class,'deleteComment']);


//Blocked User// 

Route::get('getBlockedUsers', [ServiceController::class, 'getBlockedUsers']);
Route::post('unblockUser' , [ServiceController::class, 'unblockUser']);


