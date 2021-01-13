<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Transcript;
use App\Models\Audio;

use App\Jobs\GetTranscriptFromINVOXMD;


// Esta clase permite controlar todas las peticiones HTTP de INVOX MEDICAL
class TranscriptionController extends Controller
{

    protected $API_INVOXMD_SERVER = env('API_INVOXMD_URL');
    protected $API_INVOXMD_USERNAME = env('API_INVOXMD_USERNAME');
    protected $API_INVOXMD_PASSWORD = env('API_INVOXMD_PASSWORD');

    protected $URL_TOKEN = $API_INVOXMD_SERVER . '/Transcript/v2.6/Token';
    protected $URL_TRANSCRIPT = $API_INVOXMD_SERVER . 'Transcript/v2.6/Transcript';


    private function getTokenINVOXMD()
    {
        $response = Http::asForm()->post(
            $this->URL_TOKEN,
            [
                'grant_type' => 'password',
                'username' => $this->API_INVOXMD_USERNAME,
                'password' => $this->API_INVOXMD_PASSWORD
            ]
        )->json();

        return $response['access_token'];
    }

    function getTranscriptINVOXMD($id)
    {
        $token = $this->getTokenINVOXMD();
        $URL = $this->URL_TRANSCRIPT . '/' . $id . '?username=nicolasenrique01';
        return Http::withToken($token)->get($URL)->json();
    }


    function postAudioINVOXMD($audio_base64, $id_audio)
    {
        $token = $this->getTokenINVOXMD();
        $URL = $this->URL_TRANSCRIPT . '?username=nicolasenrique01';

        $response = Http::asForm()->withToken($token)->post(
            $URL,
            [
                'Format' => 'WAV',
                'Data' => $audio_base64,
                'FileName' => $id_audio
            ]
        )->json();

        // Se registra la nueva transcripción en la base de datos
        $info = $response['Info'];
        $transcription = Transcript::create([
            'id' => $info['Id'],
            'uid' => Str::random(32),
            'status' => $info['Status'],
            'progress' => strval($info['Progress']),
            'start_date' => strtotime($info['StartDate']),
            'end_date' => null,
            'text' => $response['Text'],
            'id_audio' => $id_audio
        ]);

        dispatch((new GetTranscriptFromINVOXMD($transcription))->onQueue('transcript')->delay(30));
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


    function deleteTranscriptINVOXMD($id_audio)
    {
        $token = $this->getTokenINVOXMD();

        $transcript = Transcript::where('id_audio', $id_audio)->first();

        $API_INVOXMD_URL = env('API_INVOXMD_URL') . 'Transcript/v2.6/Transcript/' . $transcript['id'] . '?username=nicolasenrique01';

        $response = Http::withToken($token)->delete($API_INVOXMD_URL);

        return $response->status();
    }
}
