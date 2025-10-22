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
        Schema::create('items_ordenes', function (Blueprint $table) {
            
            // Tu 'id_items_ordenes SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_orden INT NOT NULL REFERENCES ordenes(id_orden) ON DELETE CASCADE'
            // Usamos la convención 'orden_id'
            $table->foreignId('orden_id')
                  ->constrained('ordenes') // Apunta a 'ordenes'
                  ->onDelete('cascade');

            // Tu 'id_producto INT NOT NULL REFERENCES productos(id_producto)'
            // Usamos la convención 'producto_id'
            $table->foreignId('producto_id')
                  ->constrained('productos'); // Apunta a 'productos'

            // Tu 'cantidad INT NOT NULL DEFAULT 1'
            $table->integer('cantidad')->default(1);

            // Tu 'precio_de_compra DECIMAL(10, 2) NOT NULL'
            $table->decimal('precio_de_compra', 10, 2);
            
            // Columnas 'created_at' y 'updated_at' de Laravel
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items_ordenes');
    }
};
