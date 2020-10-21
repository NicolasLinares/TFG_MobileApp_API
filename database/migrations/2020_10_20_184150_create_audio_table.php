<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio', function (Blueprint $table) {
            $table->integer('id', true)->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas ');
            $table->timestamp('created_at')->useCurrent()->index('created_at')->comment('Fecha de creación del elemento en la tabla');
            $table->timestamp('update_at')->nullable();
            $table->string('name', 60)->index('name')->comment('Nombre del audio');
            $table->string('url')->unique('url')->comment('Dirección donde se encuentra almacenado el audio');
            $table->string('tag', 45)->index('tag')->comment('Código de paciente');
            $table->text('description')->nullable()->comment('Descripción de la nota de voz');
            $table->text('transcription')->nullable()->comment('Transcripción asociada al audio');
            $table->integer('id_doctor')->index('id_medico')->comment('Clave ajena que identifica el médico que grabó el audio');
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
