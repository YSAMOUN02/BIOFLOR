<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Support\MediaImporter;
use Illuminate\Database\Seeder;

/**
 * Adds the images and video in the Product folder to the media library, so
 * the page and article seeders can use them. Files already in the library are
 * skipped, so re-seeding does not duplicate them.
 */
class MediaSeeder extends Seeder
{
    public function run(MediaImporter $importer): void
    {
        // Your images and video, plus generated files such as the video still.
        foreach ([base_path('Product'), storage_path('app/seed-media')] as $folder) {
            if (is_dir($folder)) {
                $importer->importFolder($folder);
            }
        }

        $this->command?->line('Media library: '.Media::count().' file(s).');
    }

    /** Library path of an imported file by its base name, or the fallback. */
    public static function path(string $name, ?string $fallback): ?string
    {
        return Media::where('path', 'like', 'library/'.$name.'.%')->value('path') ?? $fallback;
    }
}
