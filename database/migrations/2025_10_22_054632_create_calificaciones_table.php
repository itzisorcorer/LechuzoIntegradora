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
        Schema::create('calificaciones', function (Blueprint $table) {
            
            // Tu 'id_calificacion SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_estudiante INT NOT NULL REFERENCES estudiantes(id_estudiante)'
            // Usamos la convención 'estudiante_id'
            $table->foreignId('estudiante_id')
                  ->constrained('estudiantes'); // Apunta a 'estudiantes'

            // Tu 'id_vendedor INT NOT NULL REFERENCES vendedores(id_vendedor) ON DELETE CASCADE'
            // Usamos la convención 'vendedor_id'
            $table->foreignId('vendedor_id')
                  ->constrained('vendedores') // Apunta a 'vendedores'
                  ->onDelete('cascade');

            // Tu 'valor_de_calificacion INT NOT NULL CHECK (valor_de_calificacion BETWEEN 1 AND 5)'
            // Usamos unsignedTinyInteger (0-255) que es eficiente para un rating
            $table->unsignedTinyInteger('valor_de_calificacion');

            // Tu 'comentario TEXT'
            $table->text('comentario')->nullable();
            
            // Columnas 'created_at' y 'updated_at' de Laravel
            $table->timestamps();

            // Opcional: Agregar el CHECK constraint exacto de tu SQL
            // DB::statement('ALTER TABLE calificaciones ADD CONSTRAINT chk_valor_calificacion CHECK (valor_de_calificacion BETWEEN 1 AND 5)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
