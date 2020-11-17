<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Carbon\Carbon;   


// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class AudiosController extends Controller
{
    function getAll(Request $request) {

        if ($request->isJson()) {
            $doctor = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $data = Audio::where('doctor', $doctor)
                            ->orderBy('id', 'desc')
                            ->simplePaginate(10);

            /*
            $array = Audio::select('*')
                            ->where('doctor', $doctor)
                            ->get()
                            ->groupBy(function($audio) {
                                $day = Carbon::parse($audio->created_at)->translatedFormat('d');
                                if ($day < 10) {
                                    $day = $day[1];
                                }
                                $month = Carbon::parse($audio->created_at)->translatedFormat('F');
                                $year = Carbon::parse($audio->created_at)->translatedFormat('Y');
                                return $day.' de '.$month.' de '.$year;
                            });

            //return response()->json($array, 200);
            $paginated = new Paginator($array, $array->count(), 5);
            return response()->json($paginated->toArray(), 200);
            */
            return response()->json($data, 200);


        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function getAllTag($tag, Request $request) {

        if ($request->isJson()) {

            $doctor = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $data = Audio::where([
                                    ['doctor', '=', $doctor],
                                    ['tag', '=', $tag]
                                ])
                                ->orderBy('id', 'desc')
                                ->simplePaginate(10);

            return response()->json($data, 200);

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
            $data = Audio::select('tag')
                            ->distinct('tag')
                            ->where('doctor', $doctor)
                            ->orderBy('tag', 'desc')
                            ->get();
            return response()->json($data, 200);
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

            $body = $request->only('file', 'data');

            $file = $body['file'];
            $data = $body['data'];

            //Storage::disk('local')->put($data['name'], $file);
            //$url = Storage::url($data['name']);

            return response()->json(['nombre' => $data['name']], 202);
 


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
                'tag' => $data['tag'],
                'description' => $data['description'] != "" ? $data['description'] : null,
                'transcription' => null,
                'doctor' => $doctor
            ]);


            return response()->json($audio, 201);
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
                return response()->json(['error' => 'Usuario no autorizado' ], 401);
            }
            // Se borra el audio
            Audio::where('uid', $uid)->delete();

            return response()->json(['message' => 'Audio borrado correctamente'], 200);
        
        } else {
            return response()->json(['error' => 'Usuario no autorizado' ], 401);
        }
    }

    function updateDescription($uid, Request $request) {
        if ($request->isJson()) {

            $data = $request->only('description');

            $doctor = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
            ])->first();

            if($audio) {

                $audio->description = $data['description'] === "" ? null : $data['description'];
                $audio->save();

                return response()->json(['message' => 'La descripción se ha actualizado correctamente'], 201);
            } else {
                return response()->json(['error' => 'Audio no encontrado.' ], 404);
            }

        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

    function updateName($uid, Request $request) {
        if ($request->isJson()) {

            $data = $request->only('name');

            $doctor = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
            ])->first();

            if($audio) {

                if ($data['name'] !== "") {
                    $audio->name = $data['name'];
                    $audio->save();
                    return response()->json(['message' => 'El nombre se ha actualizado correctamente'], 201);
                } else {
                    return response()->json(['error' => 'El nombre introducido no es válido'], 400);
                }

            } else {
                return response()->json(['error' => 'Audio no encontrado.' ], 404);
            }

        } else {
            return response()->json(['error' => 'Usuario no autorizado.' ], 401);
        }
    }

}
