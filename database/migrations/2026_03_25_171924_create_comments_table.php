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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Relación con el Usuario (quién comenta)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Relación con el Producto (de qué cuy habla)
            $table->foreignId('guinea_pig_id')->constrained()->onDelete('cascade');
            
            $table->text('content');
            $table->integer('rating')->default(5); // Estrellas 1-5
            $table->boolean('is_active')->default(true); // Para que el admin pueda ocultarlos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
