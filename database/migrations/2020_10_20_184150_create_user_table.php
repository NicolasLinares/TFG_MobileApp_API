<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->integer('id', true)->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas ');
            $table->timestamp('created_at')->useCurrent()->comment('Fecha de creación del elemento en la tabla');
            $table->timestamp('updated_at')->nullable();
            $table->string('email')->unique('email')->comment('Correo electrónico del médico, es clave única');
            $table->string('password', 60)->comment('Contraseña para iniciar sesión en la aplicación');
            $table->string('name', 60)->index('name')->comment('Nombre del médico');
            $table->string('surnames', 60)->index('surnames')->comment('Apellidos del médico');
            $table->string('country', 60)->index('country')->comment('País donde trabaja el médico');
            $table->string('specialty', 60)->index('specialty')->comment('Especialidad médica');
            $table->index(['name', 'surnames', 'email', 'country', 'specialty']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user');
    }
}
