<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tweets', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->dropColumn([
                'name',
                'image',
                'handle',
                'comments',
                'retweets',
                'likes',
                'analytics',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tweets', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->string('handle')->nullable();
            $table->string('comments')->default('0');
            $table->string('retweets')->default('0');
            $table->string('likes')->default('0');
            $table->string('analytics')->default('0');
        });
    }
};
