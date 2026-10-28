<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Key/value site settings: brand name, logos, distributor block, social links,
 * newsletter wording. Editable from /admin/settings, so a re-brand or a new
 * market is a matter of changing rows, not templates.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 120)->unique();          // brand.name, distributor.phone, ...
            $table->string('group', 60)->default('general');
            $table->string('label');
            $table->string('type', 20)->default('text');   // text | textarea | richtext | image | url | email | boolean | number
            $table->longText('value')->nullable();
            $table->string('help')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->longText('references')->nullable()->after('body');
        });

        // Mobile mega-menu intro ("Our products — discover the range") per top-level item.
        Schema::table('nav_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('url');
            $table->string('cta_label')->nullable()->after('description');
        });

        // The newsletter form asks for a name; 'source' tells the block from the pop-in.
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('first_name', 40)->nullable()->after('email');
            $table->string('last_name', 80)->nullable()->after('first_name');
            $table->string('source', 30)->nullable()->after('consent');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('articles', fn (Blueprint $table) => $table->dropColumn('references'));
        Schema::table('nav_items', fn (Blueprint $table) => $table->dropColumn(['description', 'cta_label']));
        Schema::table('newsletter_subscribers', fn (Blueprint $table) => $table->dropColumn(['first_name', 'last_name', 'source']));
    }
};
