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
            $table->foreignId('parent_id')->nullable()->after('user_id')->constrained('tweets')->cascadeOnDelete();
            $table->foreignId('quote_of_id')->nullable()->after('parent_id')->constrained('tweets')->nullOnDelete();
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
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['quote_of_id']);
            $table->dropColumn(['parent_id', 'quote_of_id']);
        });
    }
};
