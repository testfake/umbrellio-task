<?php

use Illuminate\Http\Request;

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

Route::group( [ 'namespace' => 'Api', 'prefix' => 'v1' ], function () {

    //X-Requested-With : XMLHttpRequest

    /**
     * Create posts
     */
    Route::post( 'posts', 'PostsController@create' );

    /**
     * Rate post
     */
    Route::get( 'posts/{post}/rate/{rating}', 'PostsController@rate' );

    /**
     * Get list of the posts
     */
    Route::get( 'posts/top', 'PostsController@getTopList' );

    /**
     * Get list of the ip
     */
    Route::get( 'posts/ip', 'PostsController@getIpList' );

} );
