<?php

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
    return view('home');
});

Auth::routes([
    'register' => false,
    'verify' => false,
]);

Route::namespace('Auth')->group(function () {
    Route::get('/redirect', 'SocialAuthGoogleController@redirect');
    Route::get('/callback', 'SocialAuthGoogleController@callback');
});

Route::group(['middleware' => ['auth']], function () {
    Route::resource('bots', 'BotController')->only('index', 'destroy');
    Route::resource('users', 'UserController');
    Route::get('/list/users', 'UserController@getList');
    Route::resource('webhooks', 'WebhookController')->except([
        'show', 'update', 'destroy'
    ]);
    Route::put('webhooks/change_status', 'WebhookController@changeStatus');
    Route::resource('rooms', 'RoomController')->only([
        'index'
    ]);
});
