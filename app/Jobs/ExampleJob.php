<?php

namespace App\Jobs;


use Illuminate\Support\Str;
use App\Models\Transcript;



class ExampleJob extends Job
{

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
	sleep(15);

         Transcript::create([
            'id' => '1',
            'uid' => Str::random(32),
            'status' => 'Transcribiendo',
            'progress' => '0',
            'start_date' => null,
            'end_date' => null,
            'text' => 'Prueba',
            'id_audio' => '735'
        ]);

    }
}
