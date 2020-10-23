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
    $router->post('/auth/login', 'AuthController@login');
    $router->post('/auth/register', 'AuthController@register');

    // Todas las peticiones que se producen dentro de la app pasan a través
    // de un middleware de autenticación JWT
    $router->group(['middleware' => 'auth:api'], function($router)
    {
        $router->post('/auth/logout', ['uses' => 'AuthController@logout']);

        $router->get('users', ['uses' => 'UsersController@showUsers']);

        $router->put('user/password', ['uses' => 'UsersController@updatePassword']);
        $router->put('user/country', ['uses' => 'UsersController@updateCountry']);
        $router->put('user/speciality', ['uses' => 'UsersController@updateSpeciality']);

    });
});


/*
$router->group(['prefix' => 'api'], function () use ($router) {


});
*/
