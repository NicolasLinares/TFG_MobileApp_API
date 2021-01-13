<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Transcript;

use App\Http\Controllers\TranscriptionController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $transcription = Transcript::create([
            'id' => '1',
            'uid' => Str::random(32),
            'status' => 'Transcribiendo',
            'progress' => '0',
            'start_date' => null,
            'end_date' => null,
            'text' => 'prueba1',
            'id_audio' => 750
        ]);

    }
}
