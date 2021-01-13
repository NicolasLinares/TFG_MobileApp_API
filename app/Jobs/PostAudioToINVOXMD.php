<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Transcript;

use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

use App\Jobs\GetTranscriptFromINVOXMD;


class PostAudioToINVOXMD extends Job
{

    protected $audio_base64;
    protected $audio_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($base64, $id)
    {
        $this->audio_base64 = $base64;
        $this->audio_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Se envía el audio al servicio de transcripción
        $invoxmd_service = new TranscriptionController();
        $invoxmd_service->postAudioINVOXMD($this->audio_base64, $this->audio_id);


        /*$API_INVOXMD_URL = env('API_INVOXMD_URL');
        $API_INVOXMD_USERNAME = env('API_INVOXMD_USERNAME');
        $API_INVOXMD_PASSWORD = env('API_INVOXMD_PASSWORD');

        $response = Http::asForm()->post(
            $API_INVOXMD_URL . '/Transcript/v2.6/Token',
            [
                'grant_type' => 'password',
                'username' => $API_INVOXMD_USERNAME,
                'password' => $API_INVOXMD_PASSWORD
            ]
        )->json();

        $token =  $response['access_token'];

        $API_INVOXMD_URL = env('API_INVOXMD_URL') . 'Transcript/v2.6/Transcript?username=nicolasenrique01';



        $response = Http::asForm()->withToken($token)->post(
            $API_INVOXMD_URL,
            [
                'Format' => 'WAV',
                'Data' => $this->audio_base64,
                'FileName' =>  $this->audio_id
            ]
        )->json();


        $info = $response['Info'];

        $transcription = Transcript::create([
            'id' => $info['Id'],
            'uid' => Str::random(32),
            'status' => $info['Status'],
            'progress' => strval($info['Progress']),
            'start_date' => strtotime($info['StartDate']),
            'end_date' => null,
            'text' => $response['Text'],
            'id_audio' => $this->audio_id
        ]);

        dispatch((new GetTranscriptFromINVOXMD($transcription))->onQueue('transcript')->delay(60));
*/
    }
}
