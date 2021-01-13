<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\TranscriptionController;
use Exception;
use Illuminate\Support\Facades\Storage;

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
        // Se envía el audio al servicio de transcripción
        $invoxmd_service = new TranscriptionController();
        $invoxmd_service->postAudioINVOXMD($this->audiofile, $this->audio_id);
        Storage::disk('local')->put( '40/prueba.txt', "Testeando construct de PostAudioToINVOXMD - 1");

    }
}
