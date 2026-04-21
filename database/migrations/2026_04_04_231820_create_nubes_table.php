<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nubes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('host');
            $table->integer('puerto')->default(21);
            $table->string('usuario');
            $table->string('password');
            $table->string('ruta_raiz')->default('/');
            $table->enum('tipo_conexion', ['ftp', 'ftps', 'sftp'])->default('ftp');
            $table->boolean('ssl_pasv')->default(false);
            $table->integer('timeout')->default(30);
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nubes');
    }
};
