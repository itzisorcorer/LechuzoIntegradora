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
        Schema::create('pagos', function (Blueprint $table) {
            
            // Tu 'id_pago SERIAL PRIMARY KEY'
            $table->id();

            // Tu 'id_orden INT UNIQUE NOT NULL REFERENCES ordenes(id_orden)'
            // Esta es la relación 1 a 1 (una orden tiene un solo pago)
            $table->foreignId('orden_id')
                  ->unique() // <-- Clave para la relación 1 a 1
                  ->constrained('ordenes'); // Apunta a 'ordenes'

            // Tu 'cantidad DECIMAL(10, 2) NOT NULL'
            $table->decimal('cantidad', 10, 2);

            // Tu 'metodo VARCHAR(50) DEFAULT 'MercadoPago''
            $table->string('metodo', 50)->default('MercadoPago');

            // Tu 'id_transaccion VARCHAR(255)'
            // Es el ID que regresa MercadoPago, puede ser nulo al inicio
            $table->string('id_transaccion')->nullable();

            // --- Columnas que ya tenías ---
            
            // Tu 'status estatus_pago NOT NULL DEFAULT 'pendiente''
            // (Usando 'exitoso' como en tu SQL)
            $table->enum('status', ['pendiente', 'exitoso', 'fallido'])->default('pendiente');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
