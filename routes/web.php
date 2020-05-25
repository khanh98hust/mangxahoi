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

Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('layouts.guest');
    });
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

// Search
Route::get('/search', 'HomeController@search');

// Posts
Route::post('/posts/new', 'PostsController@create');
Route::post('/posts/like', 'PostsController@like');
Route::post('/posts/likes', 'PostsController@likes');
Route::post('/posts/comment', 'PostsController@comment');
Route::post('/posts/comments/delete', 'PostsController@deleteComment');
Route::get('/posts/list', 'PostsController@fetch');
Route::get('/post/{id}', 'PostsController@single');
Route::post('/posts/delete', 'PostsController@delete');

// Follow
Route::post('/follow', 'FollowController@follow');
Route::post('/follower/request', 'FollowController@followerRequest');
Route::post('/follower/denied', 'FollowController@followDenied');
Route::get('/followers/pending', 'FollowController@pending');

//Setting

Route::get('/settings', 'SettingsController@index');
Route::post('/settings', array(
    'as' => 'settings',
    'uses' => 'SettingsController@update'
));

// Profile
Route::get('/{username}', 'ProfileController@index');