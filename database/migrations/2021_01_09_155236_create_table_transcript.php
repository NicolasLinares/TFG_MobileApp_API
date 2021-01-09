<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableTranscript extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcript', function (Blueprint $table) {
            $table->id()->unsigned()->index()->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('filename', 32)->unique('email')->comment('Nombre de la transcripción');
            $table->string('status', 32)->comment('Estado de la transcripción (Transcribiendo/Completada)');
            $table->string('progress', 3)->index('progress')->comment('Porcentaje de progreso de la transcripción');
            $table->timestamp('start_date')->nullable()->comment('Momento en el que comenzó la transcripción');
            $table->timestamp('end_date')->nullable()->comment('Momento en el que finalizó la transcripción');
            $table->text('text')->nullable()->comment('Texto de la transcripción');


            $table->unsignedBigInteger('id_audio', false);
            $table->foreign('id_audio')->references('id')->on('audio');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcript');
    }
}
