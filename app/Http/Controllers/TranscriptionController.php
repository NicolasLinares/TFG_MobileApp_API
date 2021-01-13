<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

use App\Models\Transcript;
use App\Models\Audio;

use App\Http\Controllers\INVOXMDController;

// Esta clase permite controlar todas las peticiones HTTP de INVOX MEDICAL
class TranscriptionController extends Controller
{
    /*

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
*/

    function getTranscript($uid, Request $request)
    {
        if ($request->isJson()) {

            $doctor = Auth::id();
            // Obtiene primero el id del audio asociado
            $id_audio = Audio::select('id')->where([['doctor', '=', $doctor], ['uid', '=', $uid]])->first();
            // Obtiene la transcripción asociada al id del audio
            $transcription = Transcript::where('id_audio', $id_audio['id'])->first();

            // Si no está completada se procede a recuperarla del servicio de transcripción
            if ($transcription['status'] !== 'Completada') {

                // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
                // -----------------------------------------------------------------
                // Se recupera la transcripción de InvoxMD
                $invoxmd_service = new INVOXMDController();
                $response = $invoxmd_service->getTranscriptINVOXMD($transcription['id']);

                // BASE DE DATOS
                // -----------------------------------------------------------------
                $info = $response['Info'];
                $transcription['status'] = $info['Status'];
                $transcription['progress'] = strval($info['Progress']);
                $transcription['start_date'] = $info['StartDate'];
                $transcription['end_date'] = $info['EndDate'];
                $transcription['text'] = $response['Text'];
                $transcription->save();
            }

            return response()->json($transcription, 200);
        } else {
            return response()->json(['error' => 'Usuario no autorizado.'], 401);
        }
    }

}
