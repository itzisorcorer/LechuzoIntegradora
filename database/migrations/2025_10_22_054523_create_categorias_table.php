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
        Schema::create('categorias', function (Blueprint $table) {
            
            // Tu 'id_categoria SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'nombre VARCHAR(100) NOT NULL UNIQUE'
            // Le especificamos el límite de 100 caracteres.
            $table->string('nombre', 100)->unique();

            // Tu 'descripcion TEXT'
            // Le agregamos ->nullable() porque en tu SQL no es 'NOT NULL'
            $table->text('descripcion')->nullable();
            
            // Buena práctica de Laravel ('created_at' y 'updated_at')
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
