<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('path');                       // relative to public/uploads
            $table->string('original_name');
            $table->string('mime', 120)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('kind', 20)->default('image'); // image | video | file
            $table->timestamps();
        });

        // Uploaded artwork. Kept separate from articles.image, which names one of
        // the built-in SVG illustrations and stays as the fallback.
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
