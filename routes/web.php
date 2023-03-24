<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version(); 
});


$router->post('test', 'TaskController@test');

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->post('register', 'AuthController@register');
    $router->get('user', 'AuthController@user');
    $router->get('refresh', 'AuthController@refresh');

    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('me', 'AuthController@me');
        $router->get('logout', 'AuthController@logout');
    });

});

$router->group(['prefix' => 'task' , 'middleware' => 'auth'], function () use ($router){
    $router->post('index', 'TaskController@index');
    $router->get('index/{id}', 'TaskController@get');
    $router->post('create', 'TaskController@add');
    $router->put('update/{id}', 'TaskController@update');
    $router->put('update/{id}/assign', 'TaskController@assign');
    $router->delete('delete/{id}', 'TaskController@delete');
    $router->delete('deleteAll', 'TaskController@deleteAll');
});

$router->group(['prefix' => 'email'], function () use ($router){
    $router->group(['middleware' => ['auth','verified']], function () use ($router) {
        $router->post('request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailReguestVerification']);
    });
    $router->get('redirect',['as' => 'email.redirect', 'uses' => 'AuthController@routeEmailVerify']);
    $router->post('verify', 'AuthController@emailVerify');

    $router->group(['prefix' => 'password'], function () use ($router){
        $router->post('request-reset-password', 'AuthController@requestForgotPassword');
        $router->get('reset-redirect', ['as' => 'reset.redirect', 'uses' => 'AuthController@routeResetPassword']);
        $router->post('reset-password', 'AuthController@resetPassword');
    });

});
