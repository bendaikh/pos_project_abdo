<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'photo')) {
                DB::statement('ALTER TABLE categories MODIFY photo LONGTEXT NULL');
            }
            if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'photo')) {
                DB::statement('ALTER TABLE articles MODIFY photo LONGTEXT NULL');
            }
            if (Schema::hasTable('article_photos') && Schema::hasColumn('article_photos', 'photo_url')) {
                DB::statement('ALTER TABLE article_photos MODIFY photo_url LONGTEXT NOT NULL');
            }
            return;
        }

        if ($driver === 'pgsql') {
            if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'photo')) {
                DB::statement('ALTER TABLE categories ALTER COLUMN photo TYPE TEXT');
            }
            if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'photo')) {
                DB::statement('ALTER TABLE articles ALTER COLUMN photo TYPE TEXT');
            }
            if (Schema::hasTable('article_photos') && Schema::hasColumn('article_photos', 'photo_url')) {
                DB::statement('ALTER TABLE article_photos ALTER COLUMN photo_url TYPE TEXT');
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'photo')) {
                DB::statement('ALTER TABLE categories MODIFY photo VARCHAR(255) NULL');
            }
            if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'photo')) {
                DB::statement('ALTER TABLE articles MODIFY photo VARCHAR(255) NULL');
            }
            if (Schema::hasTable('article_photos') && Schema::hasColumn('article_photos', 'photo_url')) {
                DB::statement('ALTER TABLE article_photos MODIFY photo_url VARCHAR(255) NOT NULL');
            }
            return;
        }

        if ($driver === 'pgsql') {
            if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'photo')) {
                DB::statement('ALTER TABLE categories ALTER COLUMN photo TYPE VARCHAR(255)');
            }
            if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'photo')) {
                DB::statement('ALTER TABLE articles ALTER COLUMN photo TYPE VARCHAR(255)');
            }
            if (Schema::hasTable('article_photos') && Schema::hasColumn('article_photos', 'photo_url')) {
                DB::statement('ALTER TABLE article_photos ALTER COLUMN photo_url TYPE VARCHAR(255)');
            }
        }
    }
};
