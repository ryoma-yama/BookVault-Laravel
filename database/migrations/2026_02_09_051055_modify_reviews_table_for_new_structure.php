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
        Schema::table('reviews', function (Blueprint $table) {
            // Rename 'content' column to 'comment'
            $table->renameColumn('content', 'comment');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Change 'rating' from integer to 'is_recommended' boolean
            $table->dropColumn('rating');
            $table->boolean('is_recommended')->after('comment');
            
            // Add unique constraint for user_id and book_id combination
            $table->unique(['user_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique(['user_id', 'book_id']);
            
            // Revert is_recommended to rating
            $table->dropColumn('is_recommended');
            $table->integer('rating')->after('comment');
        });

        Schema::table('reviews', function (Blueprint $table) {
            // Rename 'comment' back to 'content'
            $table->renameColumn('comment', 'content');
        });
    }
};
