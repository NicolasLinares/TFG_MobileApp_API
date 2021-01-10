<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Transcript;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Http\Controllers\TranscriptionController;

class GetTranscriptFromINVOXMD extends Job implements SelfHandling, ShouldQueue
{

    use InteractsWithQueue, SerializesModels;

    // Id de la transcripción
    protected $id_transcript;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id_transcript = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Obtiene la transcripción asociada al id del audio
        $transcript = Transcript::where('id', $this->id_transcript)->first();

        // Si no está completada se procede a recuperarla del servicio de transcripción
        if ($transcript['status'] !== 'Completada') {

            // INVOXMD - SERVICIO DE TRANSCRIPCIÓN
            // -----------------------------------------------------------------

            // Se obtiene la transcripción por primera vez y se registra en la base de datos
            $invoxmd_service = new TranscriptionController();
            
            $response = $invoxmd_service->getTranscriptINVOXMD($this->id_transcript);

            $info = $response['Info'];

            $transcript->status = $info['Status'];
            $transcript->progress = strval($info['Progress']);
            $transcript->start_date = $info['StartDate'];
            $transcript->end_date = $info['EndDate'];
            $transcript->text = $response['Text'];
            $transcript->save();
        }
    }
}
