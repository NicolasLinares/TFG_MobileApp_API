<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAudio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio', function (Blueprint $table) {
            $table->id()->unsigned()->index()->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('name', 32)->index('name')->comment('Nombre de la nota de voz');
            $table->string('extension', 3)->comment('Extensión la nota de voz');
            $table->string('tag', 32)->index('tag')->comment('Identificador de paciente');
            $table->text('description')->nullable()->comment('Descripción de la nota de voz');
            $table->string('localpath', 32)->comment('Dirección donde se encuentra almacenada la nota de voz');
            $table->string('url', 255)->comment('Dirección donde se encuentra almacenada la nota de voz');
                    

            $table->unsignedBigInteger('doctor', false);
            $table->foreign('doctor')->references('id')->on('user');

            /*
            $table->unsignedInteger('id_transcript')->comment('Clave ajena que identifica la transcripción de una nota de voz');
            $table->unsignedInteger('id_user')->comment('Clave ajena que identifica el usuario que grabó la nota de voz');

            // Claves ajenas
            $table->foreign('id_user')->references('id')->on('user');
            $table->foreign('id_transcript')->references('id')->on('transcript');
            */
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audio');
    }
}
