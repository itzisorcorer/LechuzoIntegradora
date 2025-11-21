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
        // Agregamos la columna a la tabla 'vendedores'
        Schema::table('vendedores', function (Blueprint $table) {
            $table->string('url_foto')->nullable()->after('nombre_tienda');
        });

        // Agregamos la columna a la tabla 'estudiantes'
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('url_foto')->nullable()->after('matricula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Si revertimos, borramos las columnas
        Schema::table('vendedores', function (Blueprint $table) {
            $table->dropColumn('url_foto');
        });

        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn('url_foto');
        });
    }
};
