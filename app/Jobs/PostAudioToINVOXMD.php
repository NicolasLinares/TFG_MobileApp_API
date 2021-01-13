<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\TranscriptionController;
use Exception;

class PostAudioToINVOXMD extends Job 
{

    protected $audiofile;
    protected $audio_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($audiofile, $id)
    {
        $this->audiofile = $audiofile;
        $this->audio_id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Se envía el audio al servicio de transcripción
            $invoxmd_service = new TranscriptionController();
            $invoxmd_service->postAudioINVOXMD($this->audiofile, $this->audio_id);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ha ocurrido un problema al registrar la transcripción en la base de datos',
                'error' => $e
            ], 500);
        }

    }
}
