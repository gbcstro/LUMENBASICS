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


$router->get('/send-email', 'EmailController@sendResetPasswordEmail');



$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('tasks', 'TaskController@index');
    $router->get('/task/{id}', 'TaskController@get');
    $router->post('add', 'TaskController@add');
    $router->put('update/{id}', 'TaskController@update');
    $router->put('update/{id}/assign', 'TaskController@assign');
    $router->delete('delete/{id}', 'TaskController@delete');
    $router->delete('deleteAll', 'TaskController@deleteAll');

    $router->get('user', 'AuthController@user');
    $router->post('login', 'AuthController@login');
    $router->post('register', 'AuthController@register');
    $router->get('me', 'AuthController@me');
    $router->get('refresh', 'AuthController@refresh');
    $router->post('logout', 'AuthController@logout');
});

$router->group(['prefix' => 'email'], function () use ($router){
    $router->group(['middleware' => ['auth','verified']], function () use ($router) {
        $router->post('request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);
    });
    $router->get('redirect',['as' => 'email.redirect', 'uses' => 'AuthController@routeEmailVerify']);
    $router->post('verify', 'AuthController@emailVerify');

    $router->group(['prefix' => 'password'], function () use ($router){
        $router->post('request-reset-password', 'AuthController@requestForgotPassword');
        $router->post('reset-password', 'AuthController@resetPassword');
    });

});
