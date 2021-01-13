<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;


class PostAudioToINVOXMD extends Job
{

    protected $audio_path;
    protected $audio_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($path, $id)
    {
        $this->audio_path = $path;
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
        //$invoxmd_service = new TranscriptionController();
        //$invoxmd_service->postAudioINVOXMD($this->audio_path, $this->audio_id);


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
        )->json();

        Storage::disk('local')->put( '40/response.txt', $response);


    }
}
