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
        Schema::create('ordenes', function (Blueprint $table) {
            
            // Tu 'id_orden SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_estudiante INT NOT NULL REFERENCES estudiantes(id_estudiante)'
            // Usamos la convención de Laravel 'estudiante_id'
            $table->foreignId('estudiante_id')
                  ->constrained('estudiantes'); // Apunta a 'estudiantes'

            // Tu 'id_vendedor INT NOT NULL REFERENCES vendedores(id_vendedor)'
            // Usamos la convención de Laravel 'vendedor_id'
            $table->foreignId('vendedor_id')
                  ->constrained('vendedores'); // Apunta a 'vendedores'

            // Tu 'status estatus_orden NOT NULL DEFAULT 'pendiente''
            // ¡OJO AQUÍ! Tomé los valores de tu ENUM comentado en el SQL
            $table->enum('status', [
                'pendiente', 
                'confirmado', 
                'en_progreso', 
                'listo', 
                'completado', 
                'cancelado'
            ])->default('pendiente');

            // Tu 'cantidad_total DECIMAL(10, 2) NOT NULL'
            $table->decimal('cantidad_total', 10, 2);
            
            // Columnas 'created_at' y 'updated_at' de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};
