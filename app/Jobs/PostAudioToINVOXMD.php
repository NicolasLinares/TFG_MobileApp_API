<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\TranscriptionController;


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
    }
}
