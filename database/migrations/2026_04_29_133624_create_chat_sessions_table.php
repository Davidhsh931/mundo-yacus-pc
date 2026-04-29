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
        Schema::create('chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->json('messages');
            $table->string('intent')->nullable();
            $table->json('context')->nullable();
            $table->integer('message_count')->default(0);
            $table->timestamp('last_activity')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['session_id', 'active']);
            $table->index(['user_id', 'last_activity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_sessions');
    }
};
