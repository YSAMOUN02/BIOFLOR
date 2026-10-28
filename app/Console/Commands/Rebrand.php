<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rename a brand everywhere in the content:
 *
 *   php artisan storefront:rebrand Enterol Bioflor --dry-run
 *   php artisan storefront:rebrand Enterol Bioflor
 *
 * Replaces "Enterol", "ENTEROL" and "enterol" (so slugs and URLs follow) in
 * settings, products, attribute values, Q&A, articles, pages, menu items,
 * categories and page-section payloads. Content that uses the {brand} token
 * follows the brand.name setting and needs no rewrite.
 */
class Rebrand extends Command
{
    protected $signature = 'storefront:rebrand {from : Current brand name} {to : New brand name} {--dry-run : Count matches without saving}';

    protected $description = 'Replace a brand name across all stored content';

    /** table => text columns to rewrite */
    private const COLUMNS = [
        'settings'                 => ['value'],
        'products'                 => ['name', 'slug', 'short_description', 'meta_title', 'meta_description'],
        'product_attribute_values' => ['value'],
        'product_faqs'             => ['question', 'answer'],
        'articles'                 => ['title', 'slug', 'excerpt', 'body', 'references'],
        'pages'                    => ['title', 'subtitle', 'meta_description', 'body'],
        'faqs'                     => ['question', 'answer'],
        'nav_items'                => ['label', 'url', 'description'],
        'categories'               => ['name', 'description'],
        'page_sections'            => ['payload'],
    ];

    public function handle(): int
    {
        $from = (string) $this->argument('from');
        $to   = (string) $this->argument('to');
        $dry  = (bool) $this->option('dry-run');

        if ($from === '' || $to === '' || strcasecmp($from, $to) === 0) {
            $this->error('Give two different names.');

            return self::FAILURE;
        }

        $map = [$from => $to, mb_strtoupper($from) => mb_strtoupper($to), mb_strtolower($from) => mb_strtolower($to)];
        $total = 0;

        DB::transaction(function () use ($map, $dry, $from, &$total) {
            foreach (self::COLUMNS as $table => $columns) {
                $rows = DB::table($table)->where(function ($q) use ($columns, $from) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'like', '%'.$from.'%');   // case-insensitive collation
                    }
                })->get(array_merge(['id'], $columns));

                foreach ($rows as $row) {
                    $changes = [];
                    foreach ($columns as $column) {
                        $new = $row->{$column} === null ? null : strtr($row->{$column}, $map);
                        if ($new !== $row->{$column}) {
                            $changes[$column] = $new;
                        }
                    }
                    if ($changes) {
                        $total++;
                        if (! $dry) {
                            DB::table($table)->where('id', $row->id)->update($changes);
                        }
                    }
                }

                $this->line(sprintf('%-26s %d row(s)', $table, $rows->count()));
            }
        });

        $this->info(($dry ? 'Would update ' : 'Updated ').$total.' row(s).'.($dry ? ' Nothing saved (dry run).' : ''));

        if (! $dry) {
            $this->line('Product and article URLs changed with their slugs; old links now return 404.');
        }

        return self::SUCCESS;
    }
}
