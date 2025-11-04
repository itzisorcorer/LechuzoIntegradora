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
        Schema::create('vendedores', function (Blueprint $table) {
            
            // --- Columnas de tu SQL ---

            $table->id(); // Tu 'id_vendedor' (Laravel prefiere 'id')

            // Esta es la columna MÁS IMPORTANTE:
            // Tu 'id_usuario INT UNIQUE NOT NULL REFERENCES usuario(id_usuario) ON DELETE CASCADE'
            // En Laravel, se escribe así:
            $table->foreignId('user_id')      // Crea la columna 'user_id' (equivale a id_usuario)
                  ->unique()                 // Debe ser único (un usuario = un vendedor)
                  ->constrained('users')     // Le dice que apunta a la tabla 'users'
                  ->onDelete('cascade');     // Si se borra el user, se borra el vendedor

            $table->string('nombre_tienda'); // Tu 'nombre_tienda VARCHAR(255) NOT NULL'
            $table->text('description')->nullable(); // Tu 'description TEXT'
            $table->decimal('rating_promedio', 3, 2)->default(0.00); // Tu 'rating_promedio'

            // --- Columnas que ya tenías (¡están perfectas!) ---
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->timestamps(); // Esto crea 'created_at' y 'updated_at'

            //matricula y programa educativo
            $table->string('matricula', 10)->unique()->nullable(); // Única en Vendedores, pero opcional
            $table->foreignId('programa_educativo_id')->nullable()->constrained('programas_educativos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};
