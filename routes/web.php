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

$router->group(['prefix' => 'v1'], function($router)
{
    // LOGIN
    $router->post('/auth/login', 'AuthController@login');
    // SIGNUP
    $router->post('/auth/register', 'AuthController@register');

    // Todas las peticiones que se producen dentro de la app pasan a través
    // de un middleware de autenticación JWT
    $router->group(['middleware' => 'auth:api'], function($router)
    {   
        // LOGOUT
        $router->post('/auth/logout', ['uses' => 'AuthController@logout']);

        // USER OPS
        $router->get('users', ['uses' => 'UsersController@getAll']);

        $router->put('user/password', ['uses' => 'UsersController@updatePassword']);
        $router->put('user/country', ['uses' => 'UsersController@updateCountry']);
        $router->put('user/speciality', ['uses' => 'UsersController@updateSpeciality']);

        // AUDIO OPS
        $router->get('audios', ['uses' => 'AudiosController@getAll']);
        $router->get('tags', ['uses' => 'AudiosController@getTags']);

        $router->post('audio', ['uses' => 'AudiosController@addAudio']);
        $router->get('audio/{id}', ['uses' => 'AudiosController@getAudio']);
        $router->delete('audio/{id}', ['uses' => 'AudiosController@deleteAudio']);

        $router->put('audio/{id}/name', ['uses' => 'AudiosController@updateName']);
        $router->put('audio/{id}/tag', ['uses' => 'AudiosController@updateTag']);
        $router->put('audio/{id}/description', ['uses' => 'AudiosController@updateDescription']);
        $router->put('audio/{id}/transcription', ['uses' => 'AudiosController@updateTrascription']);

    });
});