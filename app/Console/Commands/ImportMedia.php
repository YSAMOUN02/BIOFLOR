<?php

namespace App\Console\Commands;

use App\Support\MediaImporter;
use Illuminate\Console\Command;

/** php artisan media:import Product */
class ImportMedia extends Command
{
    protected $signature = 'media:import {folder=Product : Folder with images and videos, relative to the project or absolute}';

    protected $description = 'Add the images and videos of a folder to the media library';

    public function handle(MediaImporter $importer): int
    {
        $folder = $this->argument('folder');
        $folder = preg_match('#^([a-z]:[\\\\/]|/)#i', $folder) ? $folder : base_path($folder);

        try {
            $media = $importer->importFolder($folder);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($importer->log as $line) {
            $this->line($line);
        }

        $this->info(count($media).' file(s) in the library. Pick them in Admin → Page sections, Articles or Products.');

        return self::SUCCESS;
    }
}
