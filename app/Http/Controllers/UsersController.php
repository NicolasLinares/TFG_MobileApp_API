<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class UsersController extends Controller
{
    function getAll(Request $request) {

        if ($request->isJson()) {
            return response()->json([User::all()], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }


    // Update password
    function updatePassword(Request $request) {
        if ($request->isJson()) {

            $data = $request->only('old', 'new');

            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($data, [
                'old' => 'required',
                'new' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Los datos introducidos no son correctos'], 422);
            }

            $doctor = Auth::id();
            $user = User::where('id', $doctor)->first();

            if($user) {
                if (Hash::check($data['old'], $user->password)) {
                    $user->password = Hash::make($data['new']);
                    $user->save();
                    return response()->json(['message' => 'La contraseña se ha cambiado correctamente'], 200);
                } else {
                    return response()->json(['error' => 'La contraseña actual no es correcta' ], 400);
                }
            } else {
                return response()->json(['error' => 'Usuario no autorizado' ], 401);
            }
            
        } else {
            return response()->json(['error' => 'El formato no es válido' ], 400);
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

    // Update specialty
    function updatespecialty(Request $request) {
        if ($request->isJson()) {

            $data = $request->json()->all();

            $user = User::where('email', $data['email'])->first();

            if($user) {
                $user->specialty = $data['specialty'];
                $user->save();
            }
            return response()->json([$user], 200);
        } else {
            return response()->json(['error' => 'Unauthorized' ], 401);
        }
    }

}
