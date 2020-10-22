<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Exception;

// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class UsersController extends Controller
{
    function showUsers(Request $request) {

        if ($request->isJson()) {
            return response()->json([User::all()], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Register
    function createUser(Request $request) {

        if ($request->isJson()) {
            $data = $request->json()->all();

            if (User::where('email', $data['email'])->doesntExist()) {
                // Si el usuario existe
                try{
                    $user = User::create([
                        'name'=> $data['name'],
                        'uid'=> Str::random(32),
                        'surnames'=> $data['surnames'],
                        'email'=> $data['email'],
                        'password'=> Hash::make($data['password']),
                        'speciality'=> $data['speciality'],
                        'country'=> $data['country'],
                        'api_token' => Str::random(60)
                    ]);
                    return response()->json('User succesfully created', 201);
                } catch (Exception $e) {
                    return response()->json(['error' => 'Ha ocurrido un problema en el registro' ], 500);
                }
            } else {
                return response()->json(['error' => 'El usuario ya se encuentra registrado con ese email' ], 400);
            }        
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Login
    function login(Request $request) {

        if ($request->isJson()) {
            try {
                $data = $request->json()->all();
                $user = User::where('email', $data['email'])->first();

                if ($user && Hash::check($data['password'], $user->password)) {
                    
                    if ($user->api_token == NULL) {
                        $user->api_token = Str::random(60);
                        $user->save();
                    }
                
                    return response()->json($user, 200);
                } else {
                    return response()->json(['error' => 'Incorrect password'], 400);
                }
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Unauthorized' ], 401);
            }
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Logout
    function logout(Request $request) {
        if ($request->isJson()) {

            $data = $request->json()->all();

            $user = User::where('email', $data['email'])->first();

            if($user) {
                $user->api_token = null;
                $user->save();
            }
            return response()->json([], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Update password
    function updatePassword(Request $request) {
        if ($request->isJson()) {

            $data = $request->json()->all();

            $user = User::where('email', $data['email'])->first();

            if($user) {
                $user->password = Hash::make($data['password']);
                $user->save();
            }
            return response()->json([$user], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Update country
    function updateCountry(Request $request) {
        if ($request->isJson()) {

            $data = $request->json()->all();

            $user = User::where('email', $data['email'])->first();

            if($user) {
                $user->country = $data['country'];
                $user->save();
            }
            return response()->json([$user], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

    // Update speciality
    function updateSpeciality(Request $request) {
        if ($request->isJson()) {

            $data = $request->json()->all();

            $user = User::where('email', $data['email'])->first();

            if($user) {
                $user->speciality = $data['speciality'];
                $user->save();
            }
            return response()->json([$user], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

}
