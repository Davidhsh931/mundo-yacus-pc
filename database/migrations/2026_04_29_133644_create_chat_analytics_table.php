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
        Schema::create('chat_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->nullable()->constrained('chat_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('message_type'); // user, bot
            $table->text('message');
            $table->text('response')->nullable();
            $table->string('intent')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->boolean('resolved')->default(false);
            $table->tinyInteger('satisfaction_rating')->nullable(); // 1-5 stars
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'message_type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['intent', 'resolved']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_analytics');
    }
};
