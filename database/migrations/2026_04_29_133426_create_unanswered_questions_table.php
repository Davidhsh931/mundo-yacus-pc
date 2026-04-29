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
        Schema::create('unanswered_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('cleaned_question');
            $table->integer('times_asked')->default(1);
            $table->boolean('resolved')->default(false);
            $table->timestamps();
            
            $table->index(['cleaned_question', 'times_asked']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unanswered_questions');
    }
};
