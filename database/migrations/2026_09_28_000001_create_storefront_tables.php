<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();       // en_US, fr_FR
            $table->string('url_prefix', 5);            // en, fr
            $table->string('label', 60);
            $table->string('currency_code', 5)->default('EUR');
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('nav_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('url')->nullable();
            $table->string('column_group')->nullable();  // mega-menu column heading
            $table->boolean('is_highlight')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();  // mirrors storefront product ids (11111...)
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->string('format')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('age_range')->nullable();
            $table->string('badge')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('how_to_use')->nullable();
            $table->longText('composition')->nullable();
            $table->string('accent_color', 9)->default('#001660');
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('author');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category');                  // gut-health | our-uniqueness | news
            $table->string('eyebrow')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('read_minutes')->default(3);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general');
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('meta_description')->nullable();
            $table->longText('body')->nullable();
            $table->timestamps();
        });

        // Page Designer equivalent: ordered, typed components per region.
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->index();         // home, ...
            $table->string('region')->default('main');
            $table->string('type');                      // heroBanner, tabLayout, collapsibleContent, ...
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('payload')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('retailers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 10)->default('en_US');
            $table->boolean('consent')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'contact_messages', 'newsletter_subscribers', 'retailers', 'page_sections',
            'pages', 'faqs', 'articles', 'reviews', 'product_faqs', 'products',
            'nav_items', 'locales',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
