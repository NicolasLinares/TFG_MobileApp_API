<?php

namespace App\Jobs;

use App\Http\Controllers\INVOXMDController;
use App\Jobs\Job;
use App\Models\Transcript;

class GetTranscriptFromINVOXMD extends Job
{
    // Id de la transcripción
    protected $transcription;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transcript $transc)
    {
        $this->transcription = $transc;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        if ($this->transcription['status'] !== 'Completada') {
            // Se obtiene la transcripción
            $invoxmd_service = new INVOXMDController();
            $invoxmd_service->getTranscriptINVOXMD($this->transcription);
        }
    }
}
