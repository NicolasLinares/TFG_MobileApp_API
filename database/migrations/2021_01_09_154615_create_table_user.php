<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id()->unsigned()->index('id')->comment('Clave primaria');
            $table->char('uid', 32)->unique('uid')->comment('Clave única para mejorar la seguridad al hacer consultas');
            $table->timestamps(); // created_at & updated_at

            // Datos específicos del objeto que representa la tabla
            $table->string('email')->unique('email')->comment('Correo electrónico del usuario');
            $table->string('password', 60)->comment('Contraseña para iniciar sesión en la aplicación');
            $table->string('name', 60)->index('name')->comment('Nombre del usuario');
            $table->string('surname', 60)->index('surname')->comment('Apellidos del usuario');
            $table->string('country', 32)->index('country')->comment('País donde trabaja el usuario');
            $table->string('specialty', 60)->index('specialty')->comment('Especialidad médica');
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
