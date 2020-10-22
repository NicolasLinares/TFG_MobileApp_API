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


$router->post('register', ['uses' => 'UsersController@createUser']);    
$router->post('login', ['uses' => 'UsersController@login']);

$router->group(['middleware' => ['auth']], function () use ($router) {
    $router->get('users', ['uses' => 'UsersController@showUsers']);

    $router->post('users/password', ['uses' => 'UsersController@updatePassword']);
    $router->post('users/country', ['uses' => 'UsersController@updateCountry']);
    $router->post('users/speciality', ['uses' => 'UsersController@updateSpeciality']);

    $router->post('users/logout', ['uses' => 'UsersController@logout']);
});


/*
$router->group(['prefix' => 'api'], function () use ($router) {


});
*/
