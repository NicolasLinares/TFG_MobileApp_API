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
