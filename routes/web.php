<?php

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

Route::get("/test", function () {
    return view('login');
});

Auth::routes();




// Route::post('/sigin', [App\Http\Controllers\LoginUserController::class, 'signin'])->name('loggin');
Route::group(['middleware' => ['auth']], function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::post('/different-account', [App\Http\Controllers\HomeController::class, 'differentAccount'])->name('different-account');
    Route::get('/reset-auth', [App\Http\Controllers\HomeController::class, 'resetAuth'])->name('reset-auth');
    Route::post('createAccessToken', [App\Http\Controllers\HomeController::class, 'createAccessToken']);
});

Route::get('get_spine_user', [App\Http\Controllers\UserController::class, 'get_user_from_spine']);
