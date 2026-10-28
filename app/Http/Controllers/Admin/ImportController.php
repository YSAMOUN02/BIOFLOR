<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\CatalogImporter;
use App\Support\SafeUpload;
use Database\Seeders\NavSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Product import from the admin: either the project's Product folder, or an
 * uploaded product list with its pack shots. Same importer as
 * `php artisan catalog:import`.
 */
class ImportController extends Controller
{
    public function index()
    {
        $folder = base_path('Product');

        return view('admin.import.index', [
            'folder' => $folder,
            'files'  => is_dir($folder) ? collect(File::files($folder))->map->getFilename() : collect(),
            'log'    => session('import_log', []),
        ]);
    }

    /** Preview or import the project's Product folder. */
    public function folder(Request $request, CatalogImporter $importer)
    {
        return $this->run($importer, base_path('Product'), $request->boolean('dry_run'), $request->boolean('deactivate_missing'));
    }

    /** Import an uploaded list (.xlsx/.csv) plus images. */
    public function upload(Request $request, CatalogImporter $importer)
    {
        $request->validate([
            'sheet'    => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120'],
            'images'   => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:20480'],
        ]);

        $dir = storage_path('app/imports/'.now()->format('Ymd-His'));
        File::ensureDirectoryExists($dir);

        $sheet = $request->file('sheet');
        $sheet->move($dir, 'products.'.SafeUpload::extension($sheet->getClientOriginalName(), ['xlsx', 'csv', 'txt'], 'sheet'));

        foreach ((array) $request->file('images', []) as $image) {
            SafeUpload::extension($image->getClientOriginalName(), ['jpg', 'jpeg', 'png', 'webp'], 'images');
            $image->move($dir, basename($image->getClientOriginalName()));
        }

        return $this->run($importer, $dir, false, $request->boolean('deactivate_missing'));
    }

    private function run(CatalogImporter $importer, string $folder, bool $dryRun, bool $deactivate)
    {
        try {
            $products = $importer->import($folder, $dryRun);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['import' => $e->getMessage()]);
        }

        $log = $importer->log;

        if (! $dryRun) {
            if ($deactivate) {
                $hidden = Product::whereNotIn('id', $products->pluck('id'))->where('is_active', true)->update(['is_active' => false]);
                $log[] = "Hidden {$hidden} product(s) not in the list.";
            }
            (new NavSeeder)->run();   // the products dropdown lists the active range
            $log[] = 'Menu rebuilt.';
        }

        return redirect()->route('admin.import.index')
            ->with('import_log', $log)
            ->with('status', $dryRun ? 'Preview only — nothing saved.' : $products->count().' product(s) imported.');
    }
}
