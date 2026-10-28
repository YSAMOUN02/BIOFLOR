<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\CatalogImporter;
use Illuminate\Console\Command;

/**
 * php artisan catalog:import Product
 * php artisan catalog:import "D:\new range" --dry-run
 * php artisan catalog:import Product --deactivate-missing
 */
class ImportCatalog extends Command
{
    protected $signature = 'catalog:import
        {folder=Product : Folder with the product list (.xlsx/.csv) and pack-shot images, relative to the project or absolute}
        {--dry-run : Show what would be imported without saving}
        {--deactivate-missing : Hide products that are not in this list}';

    protected $description = 'Import products, attributes and pack shots from a product folder';

    public function handle(CatalogImporter $importer): int
    {
        $folder = $this->argument('folder');
        $folder = preg_match('#^([a-z]:[\\\\/]|/)#i', $folder) ? $folder : base_path($folder);

        try {
            $products = $importer->import($folder, (bool) $this->option('dry-run'));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        foreach ($importer->log as $line) {
            $this->line($line);
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — nothing saved.');

            return self::SUCCESS;
        }

        if ($this->option('deactivate-missing')) {
            $hidden = Product::whereNotIn('id', $products->pluck('id'))->where('is_active', true)->update(['is_active' => false]);
            $this->line("Hidden {$hidden} product(s) not in the list.");
        }

        $this->info($products->count().' product(s) imported. Rebuild the menu with: php artisan db:seed --class=NavSeeder');

        return self::SUCCESS;
    }
}
