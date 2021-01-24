<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscriptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transcript', function (Blueprint $table) {
            // Datos comunes a todas las tablas
            $table->integer('id', true, false)->index('id')->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única accesible para el usuario');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('status', 32)->comment('Estado de la transcripción (Transcribiendo/Completada)');
            $table->string('progress', 3)->index('progress')->comment('Porcentaje de progreso de la transcripción');
            $table->timestamp('start_date')->nullable()->comment('Momento en el que comenzó la transcripción');
            $table->timestamp('end_date')->nullable()->comment('Momento en el que finalizó la transcripción');
            $table->text('text')->nullable()->comment('Texto de la transcripción');

            // Clave Ajena
            $table->integer('audio', false, false)->index('audio')->comment('Id del audio');
            $table->foreign('audio')->references('id')->on('audio');
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
