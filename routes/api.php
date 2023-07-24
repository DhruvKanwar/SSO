<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\API\LoginUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PortalController;




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

Route::middleware('auth:api', 'scope:view-user')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/logmeout', function (Request $request) {
    $user =  $request->user();
    $accessToken = $user->token();
    DB::table('oauth_refresh_tokens')
        ->where('access_token_id', $accessToken->id)
        ->delete();
    $user->token()->delete();


    return response()->json([
        'message' => 'Successfully logged out',
        'session' => session()->all()
    ]);
});


Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/get_user', [HomeController::class, 'get_user_info']);
    Route::get('/get_all_users', [UserController::class, 'get_all_users']);
    Route::get('/get_all_portal_admins', [UserController::class, 'get_all_portal_admins']);
    Route::get('/get_all_portals', [PortalController::class, 'get_all_portals']);
    Route::post('/assign_portal_admin', [PortalController::class, 'assign_portal_admin']);
    Route::post('/assign_portal_role', [PortalController::class, 'assign_portal_role']);
    Route::post('/remove_portal_role', [PortalController::class, 'remove_portal_role']);
    Route::post('/remove_portal_admin', [PortalController::class, 'remove_portal_admin']);
    Route::post('createAccessToken', [App\Http\Controllers\HomeController::class, 'createAccessToken']);
});
Route::get('/logout', [LoginUserController::class, 'logout']);






Route::post('/signin', [LoginUserController::class, 'signin']);
