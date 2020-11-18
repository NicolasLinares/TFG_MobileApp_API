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

use Illuminate\Support\Facades\Storage;

$router->get('storage/{directory}/{filename}', function ($directory, $filename)
    {
        return Storage::download($directory.'/'.$filename);
    }
);

// https://pln.inf.um.es/TFG_MobileApp_API/public/storage/24/file.m4a
$router->get('storage/{directory}/{filename}', function ($directory, $filename)
    {
        Storage::delete('storage/app/'.$directory.'/'.$filename);
    }
);


// https://pln.inf.um.es/TFG_MobileApp_API/public/delete/storage/24
$router->get('delete/storage/{dir}', function ($dir)
    {
        Storage::disk('local')->deleteDirectory($dir);
    }
);


// https://pln.inf.um.es/TFG_MobileApp_API/public/storage/24/file.m4a
$router->get('exists/{directory}/{filename}', function ($directory, $filename)
    {
        $value = Storage::disk('local')->exists($directory.'/'.$filename);
        return response()->json($value, 200);
    }
);

// https://pln.inf.um.es/TFG_MobileApp_API/public/storage/24/file.m4a
$router->get('rename/{directory}/{filename}', function ($directory, $filename)
    {
        Storage::disk('local')->move($directory.'/'.$filename, $directory.'/renombrado.m4a');
    }
);





$router->group(['prefix' => 'v1'], function($router)
{
    // LOGIN
    $router->post('/auth/login', 'AuthController@login');
    // SIGNUP
    $router->post('/auth/signin', 'AuthController@signin');

    // Todas las peticiones que se producen dentro de la app pasan a través
    // de un middleware de autenticación JWT
    $router->group(['middleware' => 'auth:api'], function($router)
    {   
        // LOGOUT
        $router->delete('/auth/logout', ['uses' => 'AuthController@logout']);

        // USER OPS
        $router->get('users', ['uses' => 'UsersController@getAll']);

        $router->put('user/password', ['uses' => 'UsersController@updatePassword']);
        $router->put('user/country', ['uses' => 'UsersController@updateCountry']);
        $router->put('user/speciality', ['uses' => 'UsersController@updateSpeciality']);

        // AUDIO OPS
        $router->get('audios', ['uses' => 'AudiosController@getAll']);
        $router->delete('audios', ['uses' => 'AudiosController@deleteAll']);
        $router->get('tags', ['uses' => 'AudiosController@getTags']);
        $router->get('audios/{tag}', ['uses' => 'AudiosController@getAllTag']);


        $router->post('audio', ['uses' => 'AudiosController@add']);
        $router->get('audio/{directory}/{filename}', ['uses' => 'AudiosController@downloadAudioFile']);
        $router->delete('audio/{uid}', ['uses' => 'AudiosController@delete']);
        $router->post('audio/{uid}', ['uses' => 'AudiosController@saveAudioFile']);
        
        $router->put('audio/description/{uid}', ['uses' => 'AudiosController@updateDescription']);
        $router->put('audio/name/{uid}', ['uses' => 'AudiosController@updateName']);

    });
});