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
        Schema::create('programas_educativos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->integer('rango_inicio'); // ej. 10000
            $table->integer('rango_fin');    // ej. 19999
            // No necesitamos timestamps para esta tabla
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programas_educativos');
    }
};
