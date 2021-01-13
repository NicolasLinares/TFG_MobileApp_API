<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use App\Models\Transcript;
use App\Jobs\GetTranscriptFromINVOXMD;


// Esta clase permite controlar todas las peticiones HTTP de INVOX MEDICAL
class INVOXMDController extends Controller
{

    protected $API_INVOXMD_SERVER; 
    protected $API_INVOXMD_USERNAME;
    protected $API_INVOXMD_PASSWORD;

    protected $URL_TOKEN;
    protected $URL_TRANSCRIPT;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->API_INVOXMD_SERVER = env('API_INVOXMD_URL');
        $this->API_INVOXMD_USERNAME = env('API_INVOXMD_USERNAME');
        $this->API_INVOXMD_PASSWORD = env('API_INVOXMD_PASSWORD');

        $this->URL_TOKEN = $this->API_INVOXMD_SERVER . '/Transcript/v2.6/Token';
        $this->URL_TOKEN = $this->API_INVOXMD_SERVER . 'Transcript/v2.6/Transcript';
    }


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


}
