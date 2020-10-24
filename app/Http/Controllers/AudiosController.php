<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Exception;

// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class AudiosController extends Controller
{
    function getAudios(Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::select('name','url', 'tag', 'doctor')->where('doctor', $doctor)->get();
            return response()->json([$data], 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function getTags(Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::select('tag')->distinct('tag')->where('doctor', $doctor)->get();
            return response()->json([$data], 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function getAudio($id, Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::select('*')->where('id', $id)->first();

            if($data['doctor'] != $doctor) {
                return response()->json(['error' => 'Usuario no autorizado.' ], 401);
            }
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function addAudio(Request $request)
    {

        if ($request->isJson()) {

            $data = $request->only('name', 'extension', 'url', 'tag', 'description');

            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($data, [
                'name'=> 'required',
                'extension' => 'required',
                'tag' => 'required',
                'description' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Los datos del audio no son válidos.'], 422);
            }


            $doctor = Auth::id();
            $uid = Str::random(32);
            // Evitamos que se cree un número random igual, debe ser un único
            while(Audio::where('uid',$uid)->exists()) {
                $uid = Str::random(32);
            }

            $audio = Audio::create([
                'uid'=> $uid,
                'name'=> $data['name'],
                'extension'=> $data['extension'],
                'url'=> $data['url'],
                'tag'=> $data['tag'],
                'description'=> $data['description'],
                'doctor' => $doctor
            ]);

            return response()->json([$audio], 201);

        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }
}
