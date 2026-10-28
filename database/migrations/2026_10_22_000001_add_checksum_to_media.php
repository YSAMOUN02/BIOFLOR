<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fingerprint of the source file, so importing the same image twice adds it once. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('checksum', 40)->nullable()->index()->after('kind');
            $table->unsignedInteger('width')->nullable()->after('checksum');
            $table->unsignedInteger('height')->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('media', fn (Blueprint $table) => $table->dropColumn(['checksum', 'width', 'height']));
    }
};
