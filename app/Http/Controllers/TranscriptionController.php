<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Transcript;
use App\Models\Audio;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Jobs\GetTranscriptFromINVOXMD;


// Esta clase permite controlar todas las peticiones HTTP de INVOX MEDICAL
class TranscriptionController extends Controller
{

    private function getTokenINVOXMD()
    {
        $API_INVOXMD_URL = env('API_INVOXMD_URL');
        $API_INVOXMD_USERNAME = env('API_INVOXMD_USERNAME');
        $API_INVOXMD_PASSWORD = env('API_INVOXMD_PASSWORD');

        $response = Http::asForm()->post(
            $API_INVOXMD_URL . '/Transcript/v2.6/Token',
            [
                'grant_type' => 'password',
                'username' => $API_INVOXMD_USERNAME,
                'password' => $API_INVOXMD_PASSWORD
            ]
        );

        $body = $response->json();

        return $body['access_token'];
    }

    function getTranscriptINVOXMD($id)
    {
        $token = $this->getTokenINVOXMD();

        $API_INVOXMD_URL = env('API_INVOXMD_URL') . 'Transcript/v2.6/Transcript/' . $id . '?username=nicolasenrique01';
        $response = Http::withToken($token)->get($API_INVOXMD_URL);

        return $response->json();
    }


    function postAudioINVOXMD($audiofile, $id_audio)
    {
        $token = $this->getTokenINVOXMD();

        $API_INVOXMD_URL = env('API_INVOXMD_URL') . 'Transcript/v2.6/Transcript?username=nicolasenrique01';

        $response = Http::asForm()->withToken($token)->post(
            $API_INVOXMD_URL,
            [
                'Format' => 'WAV',
                'Data' => base64_encode(file_get_contents($audiofile)),
                'FileName' => $id_audio
            ]
        )->json();


        // Se guarda la información en la base de datos
        $uid_transcript = Str::random(32);
        // Evitamos que se cree un número random igual, debe ser único
        if (Transcript::where('uid', $uid_transcript)->exists()) {
            $uid_transcript = Str::random(32);
        }

        $info = $response['Info'];

        Transcript::create([
            'id' => $info['Id'],
            'uid' => $uid_transcript,
            'status' => $info['Status'],
            'progress' => strval($info['Progress']),
            'start_date' => strtotime($info['StartDate']),
            'end_date' => null,
            'text' => $response['Text'],
            'id_audio' => $id_audio
        ]);


        $retrieveTranscript = new GetTranscriptFromINVOXMD($info['Id']);
        $this->dispatch($retrieveTranscript)->delay(30);
    }


    function deleteTranscriptINVOXMD($id_audio)
    {
        $token = $this->getTokenINVOXMD();

        $transcript = Transcript::where('id_audio', $id_audio)->first();

        $API_INVOXMD_URL = env('API_INVOXMD_URL') . 'Transcript/v2.6/Transcript/' . $transcript['id'] . '?username=nicolasenrique01';

        $response = Http::withToken($token)->delete($API_INVOXMD_URL);

        return $response->status();
    }


    function getTranscript($uid, Request $request)
    {

        if ($request->isJson()) {
            $doctor = Auth::id();
            // Obtiene primero el id del audio asociado
            $id_audio = Audio::select('id')
                ->where([
                    ['doctor', '=', $doctor],
                    ['uid', '=', $uid]
                ])
                ->first();
            
            // Obtiene la transcripción asociada al id del audio
            $transcript = Transcript::where('id_audio', $id_audio['id'])->first();

            // Si no está completada se procede a recuperarla del servicio de transcripción
            if ($transcript['status'] !== 'Completada') {
            
                // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
                // -----------------------------------------------------------------

                // Se obtiene la transcripción por primera vez y se registra en la base de datos
                $response = $this->getTranscriptINVOXMD($transcript['id']);
                
                $info = $response['Info'];

                $transcript->status = $info['Status'];
                $transcript->progress = strval($info['Progress']);
                $transcript->end_date = $info['EndDate'];
                $transcript->text = $response['Text'];
                $transcript->save();

                return response()->json($transcript, 200);
            }

            return response()->json($transcript, 200);
            
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }
}
