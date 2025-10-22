<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            
            // Tu 'id_estudiante SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_usuario INT UNIQUE NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE'
            // Usamos 'user_id' por convención de Laravel.
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users') // Apunta a la tabla 'users'
                  ->onDelete('cascade');

            // Tu 'nombre_completo VARCHAR(255) NOT NULL'
            $table->string('nombre_completo');

            // Tu 'matrícula VARCHAR(10) UNIQUE'
            // 1. Renombramos 'matrícula' a 'matricula' (sin acento) como buena práctica.
            // 2. Agregamos ->nullable() porque tu SQL no especifica 'NOT NULL'.
            // 3. Agregamos el límite de 10 caracteres.
            $table->string('matricula', 10)->unique()->nullable();

            // Columnas 'created_at' y 'updated_at' de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
