<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Http\Controllers\INVOXMDController;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use App\Jobs\PostAudioToINVOXMD;
use Exception;



// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class AudiosController extends Controller
{
    function getAll(Request $request)
    {

        if ($request->isJson()) {
            $id_user = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $history = Audio::where('doctor', $id_user)
                ->join('transcript', 'audio.id', '=', 'transcript.audio')
                ->select(['audio.*', 'transcript.text as transcription', 'transcript.status'])
                ->orderBy('audio.id', 'desc')
                ->simplePaginate(10);

            return response()->json($history, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function filterByTag($tag, Request $request)
    {
        if ($request->isJson()) {

            $id_user = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $data = Audio::where([
                ['doctor', '=', $id_user],
                ['tag', '=', $tag]
            ])
                ->join('transcript', 'audio.id', '=', 'transcript.audio')
                ->select(['audio.*', 'transcript.text as transcription', 'transcript.status'])
                ->orderBy('audio.id', 'desc')
                ->simplePaginate(10);

            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function searchByName($name, Request $request)
    {
        if ($request->isJson()) {

            $id_user = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $data = Audio::where([
                ['doctor', '=', $id_user],
                ['name', 'LIKE', '%' . $name . '%']
            ])
                ->join('transcript', 'audio.id', '=', 'transcript.audio')
                ->select(['audio.*', 'transcript.text as transcription', 'transcript.status'])
                ->orderBy('audio.id', 'desc')
                ->simplePaginate(10);
            
            return response()->json($data, 200);

        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }


    function deleteAll(Request $request)
    {

        if ($request->isJson()) {
            $id_user = Auth::id();
            // Se borran todos los audios asociados al médico
            Audio::where('doctor', $id_user)->delete();

            // Se borra todo el contenido del directorio del usuario asociado
            Storage::disk('local')->deleteDirectory($id_user);

            return response()->json(['message' => 'Todos los audios se han borrado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function getTags(Request $request)
    {

        if ($request->isJson()) {
            $id_user = Auth::id();
            $data = Audio::select('tag')
                ->distinct('tag')
                ->where('doctor', $id_user)
                ->orderBy('tag', 'desc')
                ->get();
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
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
        // VALIDACIÓN
        // -----------------------------------------------------------------
        $body = $request->all();

        // Metadata del audio (name, extension, patient code (tag), uname...)
        $data = json_decode($body['data'], true); // con true convierte a array

        // Se comprueba que los campos cumplen el formato
        $validator = Validator::make($data, [
            'name' => 'required|max:32',
            'extension' => 'required|max:3',
            'tag' => 'required|max:32',
            'uname' => 'required|max:36',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Los datos del audio no son válidos.'], 422);
        }


        // FILESYSTEM
        // -----------------------------------------------------------------

        $id_user = Auth::id();

        $audiofile = $body['file']; // archivo de audio

        $directory_name = $id_user; // user id

        $content_file = file_get_contents($audiofile);

        Storage::disk('local')->put($directory_name . '/' . $data['uname'], $content_file);

        // url para acceder al audio desde la aplicación
        $url = $request->url() . '/' . $directory_name . '/' . $data['uname'];



        // BASE DE DATOS
        // -----------------------------------------------------------------

        try {
            $audio = Audio::create([
                'uid' => Str::random(32),
                'name' => $data['name'],
                'extension' => $data['extension'],
                'uname' => $data['uname'],
                'url' => $url,
                'tag' => $data['tag'],
                'description' => $data['description'] != "" ? $data['description'] : null,
                'doctor' => $id_user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un problema al registrar la nota de voz en la base de datos',
                'error' => $e
            ], 500);
        }

        // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
        // -----------------------------------------------------------------
        $base64 = base64_encode($content_file);
        dispatch((new PostAudioToINVOXMD($base64, $audio['name'], $audio['id']))->onQueue('audio'));


        // RESPUESTA
        // -----------------------------------------------------------------
        // Se añade información sobre la transcripción
        $audio['status'] = 'Transcribiendo';
        $audio['transcription'] = '-';

        return response()->json($audio, 201);
    }



    function downloadAudioFile($uid, Request $request)
    {
        if ($request->isJson()) {
            $id_user = Auth::id();

            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $id_user]
            ])->first();

            // El directorio es el id del usuario, por tanto si el audio se encuentra 
            // en su carpeta entonces el acceso está permitido
            if ($audio) {
                return Storage::download($id_user . '/' . $audio['uname']);
            } else {
                return response()->json(['error' => 'Audio no encontrado'], 404);
            }
        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }

    function delete($uid, Request $request)
    {

        if ($request->isJson()) {
            // Se comprueba que el usuario que borra sea el dueño del audio
            $id_user = Auth::id();

            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $id_user]
            ])->first();

            if ($audio) {

                // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
                // -----------------------------------------------------------------
                // Se borra la transcripción en el servicio de transcripción

                try {
                    // Se borra la transcripción
                    $invoxmd_service = new INVOXMDController();
                    $status = $invoxmd_service->deleteTranscriptINVOXMD($audio->id);

                    if ($status !== 200) {
                        return response()->json(['error' => 'La transcripción no se ha borrado correctamente'], $status);
                    }
                } catch (Exception $e) {
                    return response()->json(['error' => 'Ha ocurrido un problema al borrar la transcripción en la base de datos '], 500);
                }


                // BASE DE DATOS
                // -----------------------------------------------------------------
                // Se borra el audio en la BBDD

                Audio::where('uid', $uid)->delete();

                // FILESYSTEM
                // -----------------------------------------------------------------
                // Se borra el audio en el filesystem
                // El directorio es el id del usuario, por tanto si el audio se encuentra 
                // en su carpeta entonces el acceso está permitido
                Storage::disk('local')->delete($id_user . '/' . $audio['uname']);


                // Número de audios que tienen el mismo código de paciente (tag)
                $n_audios = Audio::where([
                    ['tag', '=', $audio['tag']],
                    ['doctor', '=', $id_user]
                ])->count();

                return response()->json(
                    [
                        'tag' => $audio['tag'],
                        'count' => $n_audios,
                        'message' => 'Audio borrado correctamente',
                    ],
                    200
                );
            } else {
                return response()->json(['error' => 'Audio no encontrado'], 404);
            }
        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }

    function updateDescription($uid, Request $request)
    {
        if ($request->isJson()) {

            $data = $request->only('description');

            $id_user = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $id_user]
            ])->first();

            if ($audio) {

                $audio->description = $data['description'] === "" ? null : $data['description'];
                $audio->save();

                return response()->json(['message' => 'La descripción se ha actualizado correctamente'], 201);
            } else {
                return response()->json(['error' => 'Audio no encontrado'], 404);
            }
        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }

    function updateName($uid, Request $request)
    {
        if ($request->isJson()) {

            $data = $request->only('name');

            $id_user = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $id_user]
            ])->first();

            if ($audio) {

                if ($data['name'] !== "") {
                    $audio->name = $data['name'];
                    $audio->save();
                    return response()->json(['message' => 'El nombre se ha actualizado correctamente'], 201);
                } else {
                    return response()->json(['error' => 'El nombre introducido no es válido'], 400);
                }
            } else {
                return response()->json(['error' => 'Audio no encontrado'], 404);
            }
        } else {
            return response()->json(['error' => 'Usuario no autorizado'], 401);
        }
    }
}
