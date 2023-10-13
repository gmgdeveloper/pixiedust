<?php

use App\Http\Controllers\Admin\AdminLoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

// Define the route for resetting the password
Route::get('/reset-password/{token}', [AdminLoginController::class, 'resetPasswordLoad'])->name('resetPasswordLoad');
Route::post('/reset-password', [AdminLoginController::class, 'resetPassword'])->name('resetPassword');






Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
