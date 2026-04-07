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
    Schema::table('categories', function (Blueprint $table) {
        // Aquí es donde el Admin escribirá sus "lecciones" para la IA
        $table->text('training_data')->nullable(); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('training_data');
        });
    }
};
