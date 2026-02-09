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
        Schema::create('authors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('book_authors', function (Blueprint $table) {
            $table->integer('book_id');
            $table->integer('author_id');

            $table->primary(['book_id', 'author_id']);
        });

        Schema::create('book_copies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->date('acquired_date');
            $table->date('discarded_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('book_tag', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->integer('tag_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();

            $table->unique(['book_id', 'tag_id']);
        });

        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('google_id')->nullable()->unique();
            $table->string('isbn_13')->unique();
            $table->string('title');
            $table->string('publisher');
            $table->date('published_date');
            $table->text('description');
            $table->string('image_url')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->text('payload');
            $table->text('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->text('failed_job_ids');
            $table->text('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('queue')->index();
            $table->text('payload');
            $table->integer('attempts');
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at');
            $table->integer('created_at');
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_copy_id');
            $table->integer('user_id');
            $table->date('borrowed_date');
            $table->date('returned_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->integer('user_id');
            $table->text('comment');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->boolean('is_recommended');

            $table->unique(['user_id', 'book_id']);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->nullable()->index();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->dateTime('two_factor_confirmed_at')->nullable();
            $table->string('display_name')->nullable();
            $table->string('role')->default('user');
        });

        Schema::table('book_authors', function (Blueprint $table) {
            $table->foreign(['book_id'], null)->references(['id'])->on('books')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['author_id'], null)->references(['id'])->on('authors')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('book_copies', function (Blueprint $table) {
            $table->foreign(['book_id'], null)->references(['id'])->on('books')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('book_tag', function (Blueprint $table) {
            $table->foreign(['book_id'], null)->references(['id'])->on('books')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['tag_id'], null)->references(['id'])->on('tags')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreign(['book_copy_id'], null)->references(['id'])->on('book_copies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign(['book_id'], null)->references(['id'])->on('books')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['user_id'], null)->references(['id'])->on('users')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign();
            $table->dropForeign();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign();
            $table->dropForeign();
        });

        Schema::table('book_tag', function (Blueprint $table) {
            $table->dropForeign();
            $table->dropForeign();
        });

        Schema::table('book_copies', function (Blueprint $table) {
            $table->dropForeign();
        });

        Schema::table('book_authors', function (Blueprint $table) {
            $table->dropForeign();
            $table->dropForeign();
        });

        Schema::dropIfExists('users');

        Schema::dropIfExists('tags');

        Schema::dropIfExists('sessions');

        Schema::dropIfExists('reviews');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('loans');

        Schema::dropIfExists('jobs');

        Schema::dropIfExists('job_batches');

        Schema::dropIfExists('failed_jobs');

        Schema::dropIfExists('cache_locks');

        Schema::dropIfExists('cache');

        Schema::dropIfExists('books');

        Schema::dropIfExists('book_tag');

        Schema::dropIfExists('book_copies');

        Schema::dropIfExists('book_authors');

        Schema::dropIfExists('authors');
    }
};
