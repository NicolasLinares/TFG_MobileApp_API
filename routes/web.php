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

// VERSION 1
$router->group(['prefix' => 'v1'], function ($router) {
    // LOGIN & SIGNIN
    $router->post('/login',  ['uses' => 'AuthController@login']);
    $router->post('/signin',  ['uses' => 'AuthController@signin']);

    // MIDDLEWARE JWT - Filtro que autentica al usuario
    $router->group(['middleware' => 'auth:api'], function ($router) {

        // REFRESH TOKEN
        $router->put('/refresh',  ['uses' => 'AuthController@refresh']);
        // LOGOUT
        $router->delete('/logout', ['uses' => 'AuthController@logout']);

        // USER OPERATIONS
        $router->get('users', ['uses' => 'UsersController@getAll']);
        $router->put('user/password', ['uses' => 'UsersController@updatePassword']);
        $router->put('user/country', ['uses' => 'UsersController@updateCountry']);
        $router->put('user/specialty', ['uses' => 'UsersController@updatespecialty']);

        // AUDIO OPERATIONS
        $router->get('audios', ['uses' => 'AudiosController@getAll']);
        $router->delete('audios', ['uses' => 'AudiosController@deleteAll']);
        $router->get('audios/filter/{tag}', ['uses' => 'AudiosController@filterByTag']);
        $router->get('audios/search/{name}', ['uses' => 'AudiosController@searchByName']);
        $router->get('tags', ['uses' => 'AudiosController@getTags']);
        $router->post('audio', ['uses' => 'AudiosController@add']);
        $router->get('audio/{uid}', ['uses' => 'AudiosController@downloadAudioFile']);
        $router->delete('audio/{uid}', ['uses' => 'AudiosController@delete']);
        $router->put('audio/description/{uid}', ['uses' => 'AudiosController@updateDescription']);
        $router->put('audio/name/{uid}', ['uses' => 'AudiosController@updateName']);

        // TRANSCRIPT OPERATIONS
        $router->get('transcript/{uid}', ['uses' => 'TranscriptionController@getTranscript']);
    });
});








/*
Rutas usadas para administrar los archivos de audio


use Illuminate\Support\Facades\Storage;

// DOWNLOAD FILE
// https://pln.inf.um.es/TFG_MobileApp_API/public/dir/file.wav
$router->get('{directory}/{filename}', function ($directory, $filename) {
    return Storage::download($directory . '/' . $filename);
});

// RENAME FILE
// https://pln.inf.um.es/TFG_MobileApp_API/public/dir/file.wav
$router->put('rename/{directory}/{filename}', function ($directory, $filename) {
    // faltaría obtener del json el nuevo nombre
    $new_name = '/renombrado.m4a';
    Storage::disk('local')->move($directory . '/' . $filename, $directory . $new_name);
});

// DELETE FILE
// https://pln.inf.um.es/TFG_MobileApp_API/public/dir/file.wav
$router->delete('{directory}/{filename}', function ($directory, $filename) {
    Storage::disk('local')->delete($directory . '/' . $filename);
});

// DELETE ALL IN DIRECTORY
// https://pln.inf.um.es/TFG_MobileApp_API/public/dir
$router->delete('{directory}', function ($directory) {
    Storage::disk('local')->deleteDirectory($directory);
});

// CHECK IF EXISTS
// https://pln.inf.um.es/TFG_MobileApp_API/public/exists/dir
$router->get('exists/{directory}/{filename}', function ($directory, $filename) {
    $value = Storage::disk('local')->exists($directory . '/' . $filename);
    return response()->json($value, 200);
});

*/
