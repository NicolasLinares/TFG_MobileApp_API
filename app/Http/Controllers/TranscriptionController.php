<?php

namespace App\Http\Controllers;

use App\Models\Transcript;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;


use Exception;

// Esta clase permite controlar todas las peticiones HTTP de los usuarios

class TranscriptionController extends Controller
{

    function getTokenINVOXMD() {
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

    function postAudioINVOXMD($token, $audiofile, $fileName) {
        $API_INVOXMD_URL = env('API_INVOXMD_URL').'Transcript/v2.6/Transcript?username=nicolasenrique01';

        $response = Http::asForm()->withToken($token)->post($API_INVOXMD_URL,
            [
                'Format' => 'WAV',
                'Data' => base64_encode(file_get_contents($audiofile)),
                'FileName' => $fileName
            ]);

        return $response->json();
    }

    function getTranscriptINVOXMD($token, $id) {
        $API_INVOXMD_URL = env('API_INVOXMD_URL').'Transcript/v2.6/Transcript/'.$id.'?username=nicolasenrique01';

        $response = Http::withToken($token)->get($API_INVOXMD_URL);

        return $response->json();
    }

}
