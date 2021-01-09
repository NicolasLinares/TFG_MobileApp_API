<?php

namespace App\Http\Controllers;

use App\Models\Audio;
use App\Models\Transcript;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

use App\Http\Controllers\Controller;
use Exception;


// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class AudiosController extends Controller
{
    function getAll(Request $request)
    {

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
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function filterByTag($tag, Request $request)
    {
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
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function searchByName($name, Request $request)
    {
        if ($request->isJson()) {

            $doctor = Auth::id();

            // Paginación ordenada de forma descendente (primero los audios más recientes)
            $data = Audio::where([
                ['doctor', '=', $doctor],
                ['name', 'LIKE', '%'.$name.'%']
            ])
            ->orderBy('id', 'desc')
            ->simplePaginate(10);

            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }


    function deleteAll(Request $request)
    {

        if ($request->isJson()) {
            $doctor = Auth::id();
            // Se borran todos los audios asociados al médico
            Audio::where('doctor', $doctor)->delete();

            // Se borra todo el contenido del directorio del usuario asociado
            Storage::disk('local')->deleteDirectory($doctor);

            return response()->json(['message' => 'Todos los audios se han borrado correctamente.'], 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

    function getTags(Request $request)
    {

        if ($request->isJson()) {
            $doctor = Auth::id();
            $data = Audio::select('tag')
                ->distinct('tag')
                ->where('doctor', $doctor)
                ->orderBy('tag', 'desc')
                ->get();
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }


    private function getTokenINVOXMD() {
        $API_INVOXMD_URL = env('API_INVOXMD_URL');
        $API_INVOXMD_USERNAME = env('API_INVOXMD_USERNAME');
        $API_INVOXMD_PASSWORD = env('API_INVOXMD_PASSWORD');

        $response = Http::asForm()->post($API_INVOXMD_URL.'/Transcript/v2.6/Token',
            [
                'grant_type' => 'password',
                'username' => $API_INVOXMD_USERNAME,
                'password' => $API_INVOXMD_PASSWORD
            ]);

        $body = $response->json();

        return $body['access_token'];
    }

    private function postAudioINVOXMD($token, $audiofile, $fileName) {
        $API_INVOXMD_URL = env('API_INVOXMD_URL');

        $response = Http::asForm()->withToken($token)->post($API_INVOXMD_URL.'/Transcript/v2.6/Transcript?username=nicolasenrique01',
            [
                'Format' => 'WAV',
                'Data' => base64_encode(file_get_contents($audiofile)),
                'FileName' => $fileName
            ]);

        return $response->json();
    }


    /**
     * Store a new audio.
     *
     * @param  Request  $request
     * @return Response
     */
    public function add(Request $request)
    {

        $body = $request->all();

        // con true convierte a array
        $data = json_decode($body['data'], true); // información del audio (name, extension, patient code, localpath...)


        // Se comprueba que los campos cumplen el formato
        $validator = Validator::make($data, [
            'name' => 'required',
            'extension' => 'required',
            'tag' => 'required',
            'localpath' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Los datos del audio no son válidos.'], 422);
        }


        // FILESYSTEM
        // -----------------------------------------------------------------

        $doctor = Auth::id();

        $audiofile = $body['file']; // archivo de audio
        $directory_name = $doctor; // user id

        Storage::disk('local')->put($directory_name . '/' . $data['localpath'], file_get_contents($audiofile));

        // url para acceder al audio desde la aplicación
        $url = $request->url() . '/' . $directory_name . '/' . $data['localpath'];



        // BASE DE DATOS
        // -----------------------------------------------------------------

        try {
            $uid_audio = Str::random(32);
            // Evitamos que se cree un número random igual, debe ser único
            if (Audio::where('uid', $uid_audio)->exists()) {
                $uid_audio = Str::random(32);
            }

            $audio = Audio::create([
                'uid' => $uid_audio,
                'name' => $data['name'],
                'extension' => $data['extension'],
                'localpath' => $data['localpath'],
                'url' => $url,
                'tag' => $data['tag'],
                'description' => $data['description'] != "" ? $data['description'] : null,
                'doctor' => $doctor
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Ha ocurrido un problema al registrar la nota de voz en la base de datos' ], 500);
        }

        // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
        // -----------------------------------------------------------------


        try{
            // Se obtiene el token de autorización
            $INVOXMD_token = $this->getTokenINVOXMD();
            // Se envía el audio
            $response = $this->postAudioINVOXMD($INVOXMD_token, $audiofile, $data['name']);
            
            // Se guarda la información en la base de datos
            $uid_transcript = Str::random(32);
            // Evitamos que se cree un número random igual, debe ser único
            if (Transcript::where('uid', $uid_transcript)->exists()) {
                $uid_transcript = Str::random(32);
            }
    
            $info = $response['Info'];
            Transcript::create([
                'id' => $info->Id,
                'uid' => $uid_transcript,
                'filename' => $info->FileName,
                'status' => $info->Status,
                'progress' => "0",
                'start_date' => null,
                'end_date' => null,
                'text' => $response->Text,
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => 'Ha ocurrido un problema al registrar la transcripción en la base de datos' ], 500);
        }


        return response()->json($audio, 201);
    }

    

    function downloadAudioFile($uid, Request $request)
    {
        if ($request->isJson()) {
            $doctor = Auth::id();

            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
            ])->first();

            // El directorio es el id del usuario, por tanto si el audio se encuentra 
            // en su carpeta entonces el acceso está permitido
            if ($audio) {
                return Storage::download($doctor . '/' . $audio['localpath']);
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
            $doctor = Auth::id();

            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
            ])->first();

            if ($audio) {
                // Se borra el audio en la BBDD
                Audio::where('uid', $uid)->delete();
                // Se borra el audio en el filesystem
                // El directorio es el id del usuario, por tanto si el audio se encuentra 
                // en su carpeta entonces el acceso está permitido
                Storage::disk('local')->delete($doctor . '/' . $audio['localpath']);

                // Número de audios que tienen el mismo código de paciente (tag)
                $n_audios = Audio::where([
                    ['tag', '=', $audio['tag']],
                    ['doctor', '=', $doctor]
                ])->count();

                return response()->json(
                    [
                        'tag' => $audio['tag'],
                        'count' => $n_audios,
                        'message' => 'Audio borrado correctamente',
                    ], 
                    200);
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

            $doctor = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
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

            $doctor = Auth::id();
            $audio = Audio::where([
                ['uid', '=', $uid],
                ['doctor', '=', $doctor]
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
