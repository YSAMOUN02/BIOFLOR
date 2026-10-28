<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Content in other languages.
 *
 * translations: one row per record × language × field (products, articles,
 *   pages, menu items, Q&A, categories, attribute names). Missing rows fall
 *   back to the default-language column.
 * page_sections.translations: {"km": {...}} — the section's text in another
 *   language, laid over the payload; images and links stay shared.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 10);                  // url prefix: km, fr
            $table->string('field', 60);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translations_unique');
            $table->index(['translatable_type', 'locale']);
        });

        Schema::table('page_sections', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('payload');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
        Schema::table('page_sections', fn (Blueprint $table) => $table->dropColumn('translations'));
    }
};
