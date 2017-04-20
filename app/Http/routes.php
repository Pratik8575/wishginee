<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
Route::group(['middleware' => 'api', 'prefix' => 'api'], function(){
    Route::get('/', function (){
        return "Use Wishginee API Endpoints.";
    });
    Route::post('/auth', 'AuthController@authenticate');
    Route::post('/users', 'AuthController@signUp');

    Route::group(['prefix' => 'users', 'middleware' => 'auth'], function (){
        Route::get('/{id}','UsersController@show');
        Route::put('/{id}', 'UsersController@update');
        Route::delete('/{id}', 'UsersController@delete');
    });

    Route::group(['prefix' => 'campaigns', 'middleware' => 'auth'], function (){
        Route::post('/', 'CampaignsController@create');
        Route::get('/', 'CampaignsController@show');
        Route::get('/{id}', 'CampaignsController@showCampaignById');
        Route::post('/approve/{id}', 'CampaignsController@updateStatus');
        Route::put('/{id}', 'CampaignsController@update');
        Route::delete('/{id}', 'CampaignsController@delete');
    });

    Route::group(['prefix' => 'events', 'middleware' => 'auth'], function (){
        Route::post('/', 'EventsController@create');
        Route::get('/', 'EventsController@show');
        Route::get('/{id}', 'EventsController@getById');
        Route::post('/approve/{id}', 'EventsController@updateStatus');
        Route::put('/{id}', 'EventsController@update');
        Route::delete('/{id}', 'EventsController@delete');
    });
    
    Route::group(['prefix' => 'follows', 'middleware' => 'auth'], function (){
        Route::post('/{id}', 'FollowsController@create');
        Route::delete('/{id}', 'FollowsController@delete');
    });
    
    Route::group(['prefix' => 'comments', 'middleware' => 'auth'], function (){
        Route::post('/{id}', 'CommentsController@create');
        Route::put('/{id}', 'CommentsController@update');
        Route::delete('/{id}', 'CommentsController@delete');
    });

    Route::group(['prefix' => 'notifications', 'middleware' => 'auth'], function (){
        Route::get('/{user_id}', 'NotificationController@getUserNotifications');
        Route::put('/{user_id}', 'NotificationController@updateSeen');
    });

    Route::group(['prefix' => 'feeds', 'middleware' => 'auth'], function (){
        Route::get('/', 'NotificationController@getFeeds');
    });


    Route::group(['prefix' => 'sockets', 'middleware' => 'web'], function (){
        Route::get('/', 'NotificationController@getAllClients');
        Route::get('/{user_id}', 'NotificationController@getUserSocket');
        Route::post('/', 'NotificationController@addUserConnected');
        Route::delete('/{user_id}', 'NotificationController@deleteUserSocket');
    });

    Route::group(['prefix' => 'payments', 'middleware' => 'auth'], function (){
        Route::post('/capture', 'DonationsController@capture');
        Route::post('/refund/{user_id}/{payment_id}', 'DonationsController@refund');
    });

    Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function(){
        Route::get('/campaigns', 'AdminController@getAllCampaignsDetails');
        Route::get('/events', 'AdminController@getAllEventsDetails');
        Route::get('/users', 'AdminController@getAllUsersDetails');
        Route::get('/donations', 'AdminController@getAllDonationDetails');
    });
});

