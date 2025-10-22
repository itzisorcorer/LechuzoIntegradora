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
        Schema::create('productos', function (Blueprint $table) {
            
            // Tu 'id_producto SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_vendedor INT NOT NULL REFERENCES vendedores(id_vendedor) ON DELETE CASCADE'
            // Usamos 'vendedor_id' (convención de Laravel)
            $table->foreignId('vendedor_id')
                  ->constrained('vendedores') // Apunta a la tabla 'vendedores'
                  ->onDelete('cascade');

            // Tu 'id_categoria INT NOT NULL REFERENCES categorias(id_categoria)'
            // Usamos 'categoria_id' (convención de Laravel)
            $table->foreignId('categoria_id')
                  ->constrained('categorias'); // Apunta a la tabla 'categorias'

            // Tu 'nombre VARCHAR(255) NOT NULL'
            $table->string('nombre');

            // Tu 'descripcion TEXT'
            $table->text('descripcion')->nullable();

            // Tu 'precio DECIMAL(10, 2) NOT NULL'
            $table->decimal('precio', 10, 2);

            // Tu 'cantidad_disponible INT DEFAULT 0'
            $table->integer('cantidad_disponible')->default(0);

            // Tu 'disponible BOOLEAN DEFAULT TRUE'
            $table->boolean('disponible')->default(true);

            // Tu 'url_imagen VARCHAR(255)'
            $table->string('url_imagen')->nullable();
            
            // Columnas 'created_at' y 'updated_at' de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
