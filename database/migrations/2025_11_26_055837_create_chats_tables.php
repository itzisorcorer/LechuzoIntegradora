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
        Schema::create('chats', function (Blueprint $table) {
            //tabla de chats
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('vendedor_id');
            $table->timestamps();

            //foraneas:
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->onDelete('cascade');
            $table->foreign('vendedor_id')->references('id')->on('vendedores')->onDelete('cascade');

            // Evitar chats duplicados entre las mismas dos personas
            $table->unique(['estudiante_id', 'vendedor_id']);
        });

            //tabla de mensajes
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('sender_id'); // ID del User que escribió (puede ser estudiante o vendedor)
            $table->text('content');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('chats');
    }
};
