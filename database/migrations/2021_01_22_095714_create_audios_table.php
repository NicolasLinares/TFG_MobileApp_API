<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio', function (Blueprint $table) {
            // Datos comunes a todas las tablas
            $table->integer('id', true, false)->index('id')->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única accesible para el usuario');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('name', 32)->index('name')->comment('Nombre de la nota de voz');
            $table->string('extension', 3)->comment('Extensión la nota de voz');
            $table->string('tag', 32)->index('tag')->comment('Identificador de paciente');
            $table->string('uname', 36)->comment('Nombre no modificable del archivo de audio');
            $table->string('url', 255)->comment('Dirección URL del archivo de audio');
            $table->text('description')->nullable()->comment('Descripción de la nota de voz');
            
            // Clave Ajena
            $table->integer('doctor', false, false)->index('doctor')->comment('Id del usuario');
            $table->foreign('doctor')->references('id')->on('user');
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
