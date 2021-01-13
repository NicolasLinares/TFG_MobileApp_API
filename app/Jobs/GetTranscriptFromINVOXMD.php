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

            // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
            // -----------------------------------------------------------------
            // Se obtiene la transcripción por primera vez y se registra en la base de datos
            $invoxmd_service = new INVOXMDController();
            $response = $invoxmd_service->getTranscriptINVOXMD($this->transcription['id']);

            // BASE DE DATOS
            // -----------------------------------------------------------------
            $info = $response['Info'];
            $this->transcription['status'] = $info['Status'];
            $this->transcription['progress'] = strval($info['Progress']);
            $this->transcription['start_date'] = $info['StartDate'];
            $this->transcription['end_date'] = $info['EndDate'];
            $this->transcription['text'] = $response['Text'];
            $this->transcription->save();
        }
    }
}
