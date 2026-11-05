<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tweet_id')->nullable()->constrained('tweets')->nullOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason'); // spam | harassment | hate_speech | misinformation | nsfw | other
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'resolved', 'dismissed'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // A user can only report the same tweet/user once
            $table->unique(['reporter_id', 'tweet_id'], 'unique_tweet_report');
            $table->unique(['reporter_id', 'reported_user_id'], 'unique_user_report');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
