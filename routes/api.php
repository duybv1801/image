<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
    Route::post('/change-pass', [AuthController::class, 'changePassWord']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('albums', 'App\Http\Controllers\AlbumController@index')->name('albums.index');
    Route::post('albums', 'App\Http\Controllers\AlbumController@store')->name('albums.store');
    Route::get('albums/{id}', 'App\Http\Controllers\AlbumController@show')->name('albums.show');
    Route::post('albums/{id}', 'App\Http\Controllers\AlbumController@update')->name('albums.update');
    Route::delete('albums/{id}', 'App\Http\Controllers\AlbumController@destroy')->name('albums.destroy');
});
