<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\File;

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
        $invoxmd_service = new TranscriptionController();

        $audiofile = new File($this->audio_path);
        $invoxmd_service->postAudioINVOXMD($audiofile, $this->audio_id);
    }
}
