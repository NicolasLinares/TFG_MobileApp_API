<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->integer('id', true, false)->index('id')->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('name', 60)->index('name')->comment('Nombre');
            $table->string('surname', 60)->index('surname')->comment('Apellidos');
            $table->string('country', 60)->index('country')->comment('País donde trabaja');
            $table->string('specialty', 60)->index('specialty')->comment('Especialidad médica');
            $table->string('email', 255)->unique('email')->comment('Correo electrónico');
            $table->string('password', 60)->comment('Contraseña para iniciar sesión en la aplicación');
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
