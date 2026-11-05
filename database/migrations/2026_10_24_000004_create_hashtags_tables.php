<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->string('tag')->unique();
            $table->timestamps();
        });

        Schema::create('hashtag_tweet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hashtag_id')->constrained('hashtags')->cascadeOnDelete();
            $table->foreignId('tweet_id')->constrained('tweets')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['hashtag_id', 'tweet_id']);
            $table->index('tweet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtag_tweet');
        Schema::dropIfExists('hashtags');
    }
};

