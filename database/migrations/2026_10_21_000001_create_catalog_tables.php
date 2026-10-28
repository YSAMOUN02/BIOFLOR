<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reusable catalogue model.
 *
 *  categories ─┬─ category_product ─── products ─── product_images
 *              │                          │
 *  attribute_sets ── attribute_attribute_set ── attributes ── attribute_groups
 *                                         │           │
 *                                 product_attribute_values
 *
 * Product-specific fields that used to be columns on `products` become typed
 * attribute values, so a different range (other dosage forms, a food
 * supplement, a cosmetic…) can be loaded next time without a schema change.
 */
return new class extends Migration
{
    /** Legacy product column => attribute it moves into. */
    private const LEGACY = [
        'tagline'      => ['code' => 'tagline',     'name' => 'Tagline',      'type' => 'text',     'group' => 'hidden'],
        'badge'        => ['code' => 'badge',       'name' => 'Badge',        'type' => 'text',     'group' => 'hidden'],
        'format'       => ['code' => 'dosage_form', 'name' => 'Dosage form',  'type' => 'text',     'group' => 'specs'],
        'pack_size'    => ['code' => 'pack_size',   'name' => 'Pack size',    'type' => 'text',     'group' => 'labels'],
        'age_range'    => ['code' => 'age_range',   'name' => 'Suitable for', 'type' => 'text',     'group' => 'labels'],
        'description'  => ['code' => 'description', 'name' => 'Description',  'type' => 'richtext', 'group' => 'accordion'],
        'how_to_use'   => ['code' => 'dosage',      'name' => 'Dosage',       'type' => 'richtext', 'group' => 'accordion'],
        'composition'  => ['code' => 'ingredients', 'name' => 'Ingredients',  'type' => 'richtext', 'group' => 'accordion'],
        'accent_color' => ['code' => 'pack_color',  'name' => 'Pack colour',  'type' => 'color',    'group' => 'hidden'],
    ];

    /** Display groups: code => [name, sort]. The code doubles as the display slot. */
    private const GROUPS = [
        'labels'    => ['Labels', 10],
        'badges'    => ['Badges', 20],
        'specs'     => ['Key facts', 30],
        'accordion' => ['Product information', 40],
        'steps'     => ['How to use', 50],
        'hidden'    => ['Internal', 90],
    ];

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('code', 60)->unique();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['category_id', 'product_id']);
        });

        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->string('display', 20)->default('specs');   // labels | badges | specs | accordion | steps | hidden
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_group_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->string('type', 20)->default('text');       // see ProductAttribute::TYPES
            $table->string('unit', 20)->nullable();
            $table->json('options')->nullable();               // select / multiselect: {"value": "Label"}
            $table->string('icon', 60)->nullable();            // badge icon key
            $table->string('help')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_sets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('attribute_attribute_set', function (Blueprint $table) {
            $table->foreignId('attribute_set_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->primary(['attribute_set_id', 'attribute_id']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10)->default('');         // '' = every locale; en_US / fr_FR override it
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id', 'locale']);
            $table->index(['attribute_id', 'locale']);
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->string('path');                            // relative to public/uploads
            $table->string('alt')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('attribute_set_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('meta_title')->nullable()->after('short_description');
            $table->string('meta_description')->nullable()->after('meta_title');
        });

        $this->moveLegacyColumns();
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('tagline')->nullable();
            $table->string('format')->nullable();
            $table->string('pack_size')->nullable();
            $table->string('age_range')->nullable();
            $table->string('badge')->nullable();
            $table->longText('description')->nullable();
            $table->longText('how_to_use')->nullable();
            $table->longText('composition')->nullable();
            $table->string('accent_color', 9)->default('#001660');
            $table->string('image')->nullable();
        });

        // Copy the language-neutral values back into their old columns.
        foreach (self::LEGACY as $column => $definition) {
            $attributeId = DB::table('attributes')->where('code', $definition['code'])->value('id');

            if (! $attributeId) {
                continue;
            }

            DB::table('product_attribute_values')
                ->where('attribute_id', $attributeId)
                ->where('locale', '')
                ->get(['product_id', 'value'])
                ->each(fn ($row) => DB::table('products')
                    ->where('id', $row->product_id)
                    ->update([$column => $column === 'accent_color' ? substr((string) $row->value, 0, 9) : $row->value]));
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attribute_set_id');
            $table->dropColumn(['meta_title', 'meta_description']);
        });

        foreach ([
            'product_images', 'product_attribute_values', 'attribute_attribute_set',
            'attribute_sets', 'attributes', 'attribute_groups', 'category_product', 'categories',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    /** Turn the old product columns into attribute values, then drop them. */
    private function moveLegacyColumns(): void
    {
        $now = now();

        foreach (self::GROUPS as $code => [$name, $sort]) {
            DB::table('attribute_groups')->insertOrIgnore([
                'code' => $code, 'name' => $name, 'display' => $code, 'sort_order' => $sort,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $groupIds = DB::table('attribute_groups')->pluck('id', 'code');

        foreach (self::LEGACY as $column => $definition) {
            if (! Schema::hasColumn('products', $column)) {
                continue;
            }

            DB::table('attributes')->insertOrIgnore([
                'attribute_group_id' => $groupIds[$definition['group']] ?? null,
                'code'       => $definition['code'],
                'name'       => $definition['name'],
                'type'       => $definition['type'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $attributeId = DB::table('attributes')->where('code', $definition['code'])->value('id');

            DB::table('products')
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->get(['id', $column])
                ->each(fn ($row) => DB::table('product_attribute_values')->updateOrInsert(
                    ['product_id' => $row->id, 'attribute_id' => $attributeId, 'locale' => ''],
                    ['value' => $row->{$column}, 'created_at' => $now, 'updated_at' => $now],
                ));
        }

        $drop = array_values(array_filter(
            [...array_keys(self::LEGACY), 'image'],
            fn (string $column) => Schema::hasColumn('products', $column),
        ));

        if ($drop) {
            Schema::table('products', fn (Blueprint $table) => $table->dropColumn($drop));
        }
    }
};
