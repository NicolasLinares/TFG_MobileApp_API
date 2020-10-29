<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Exception;
use phpDocumentor\Reflection\Types\Null_;

// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class AudiosController extends Controller
{
    function getAll(Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::select('name','url', 'tag', 'doctor')->where('doctor', $doctor)->get();
            return response()->json([$data], 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function deleteAll(Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            // Se borran todos los audios asociados al médico
            Audio::where('doctor', $doctor)->delete();
            return response()->json(['message' => 'Todos los audios se han borrado correctamente.'], 200);
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

    /**
     * Store a new audio.
     *
     * @param  Request  $request
     * @return Response
     */
    public function add(Request $request)
    {
        if ($request->isJson()) {

            $data = $request->only('name', 'extension', 'localpath', 'tag', 'description');

            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($data, [
                'name'=> 'required',
                'extension' => 'required',
                'tag' => 'required',
                'localpath' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => 'Los datos del audio no son válidos.'], 422);
            }

            $doctor = Auth::id();
            $uid = Str::random(32);
            // Evitamos que se cree un número random igual, debe ser único
            if(Audio::where('uid',$uid)->exists()) {
                $uid = Str::random(32);
            }

            $audio = Audio::create([
                'uid'=> $uid,
                'name'=> $data['name'],
                'extension'=> $data['extension'], 
                'localpath' => $data['localpath'],               
                'url' => null,             
                'tag'=> $data['tag'],
                'description'=> $data['description'] != "" ? $data['description'] : null,
                'doctor' => $doctor
            ]);



            
            return response()->json($audio, 201);

        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    public function saveAudioFile(Request $request)
    {

        $file = $request->file('file');

        return response(dd($file), 200);
    }

    function get($uid, Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::where('uid', $uid)->first();

            if($data['doctor'] != $doctor) {
                return response()->json(['error' => 'Usuario no autorizado.' ], 401);
            }
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function delete($uid, Request $request) {

        if ($request->isJson()) {
            // Se comprueba que el usuario que borra sea el dueño del audio
            $doctor = Auth::id();
            $user = Audio::select('doctor')->where('uid', $uid)->first();

            if($user['doctor'] != $doctor) {
                return response()->json(['error' => 'Usuario no autorizado.' ], 401);
            }
            // Se borra el audio
            Audio::where('uid', $uid)->delete();

            return response()->json(['message' => 'Audio borrado correctamente.'], 200);
        
        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function update($uid, Request $request) {
        if ($request->isJson()) {

            $data = $request->only('name', 'tag', 'description', 'transcription');

            // Se comprueba que los campos cumplen el formato
            $validator = Validator::make($data, [
                'name'=> 'required',
                'tag'=> 'required',
                'description'=> 'required',
                'transcription'=> 'required',
            ]);
            
            $doctor = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
            ])->first();

            if($audio) {
                if ($data['name'] != "") {
                    $audio->name = $data['name'];
                }
                if ($data['tag'] != "") {
                    $audio->tag = $data['tag'];
                }
                if ($data['description'] != "") {
                    $audio->description = $data['description'];
                }
                if ($data['transcription'] != "") {
                    $audio->transcription = $data['transcription'];
                }
                $audio->save();
                return response()->json($audio, 201);
            } else {
                return response()->json(['error' => 'Audio no encontrado.' ], 404);
            }

        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

}
