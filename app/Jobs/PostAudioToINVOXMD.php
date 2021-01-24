<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Http\Controllers\INVOXMDController;

class PostAudioToINVOXMD extends Job
{

    protected $audio_base64;
    protected $audio_name;
    protected $audio_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($base64, $name, $id)
    {
        $this->audio_base64 = $base64;
        $this->audio_name = $name;
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
        $invoxmd_service = new INVOXMDController();
        $invoxmd_service->postAudioINVOXMD($this->audio_base64, $this->audio_name, $this->audio_id);
    }

}
