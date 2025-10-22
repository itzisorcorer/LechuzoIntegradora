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
        // Esta es la tabla que coincide con tu 'usuario'
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Equivale a 'id_usuario SERIAL PRIMARY KEY'. Laravel prefiere 'id'
            
            // $table->string('name'); // <--- ¡Esta es la línea que eliminamos!

            $table->string('email')->unique(); // Coincide con tu 'email'
            $table->timestamp('email_verified_at')->nullable(); // Columna de Laravel para verificar email
            
            // Laravel espera que la columna se llame 'password' para la autenticación
            $table->string('password'); // Equivale a tu 'password_hash'

            // Coincide con tu ENUM 'rol_usuario'. Laravel lo manejará bien.
            $table->enum('role',['modulo', 'vendedor', 'admin'])->default('vendedor');

            $table->rememberToken(); // Columna de Laravel para la función "Recordarme"
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' de Laravel
        });

        // Estas tablas son para el sistema de "Resetear Contraseña" y "Sesiones"
        // Déjalas exactamente como están. Son necesarias para Laravel.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
