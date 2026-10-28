<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeGroup;
use App\Models\AttributeSet;
use App\Models\Category;
use App\Models\Locale;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Products: core fields, categories, attribute values (grouped as on the
 * site) and the image gallery, all on one form.
 */
class ProductCrudController extends CrudController
{
    protected string $model      = Product::class;
    protected string $routeName  = 'admin.products';
    protected string $title      = 'Products';
    protected string $singular   = 'Product';
    protected string $orderBy    = 'sort_order';
    protected array  $searchable = ['name', 'slug'];
    protected string $formView   = 'admin.products.form';

    protected function columns(): array
    {
        return [
            'id'         => 'Ref',
            'name'       => 'Name',
            'slug'       => 'Slug',
            'sort_order' => 'Order',
            'is_active'  => 'Active',
        ];
    }

    protected function fields(): array
    {
        return [
            'id'                => ['type' => 'number', 'label' => 'Reference (product id)', 'help' => 'Used in the URL: /en/{slug}/{id}.html — cannot change later.', 'readonly_on_edit' => true],
            'name'              => ['type' => 'text', 'label' => 'Name', 'help' => 'HTML allowed for the trademark sign, e.g. '.brand().'&reg; 250mg Sachets'],
            'slug'              => ['type' => 'text', 'label' => 'Slug'],
            'attribute_set_id'  => ['type' => 'select', 'label' => 'Product type', 'options' => ['' => '— All attributes —'] + AttributeSet::orderBy('name')->pluck('name', 'id')->all(),
                                    'help' => 'Decides which attributes are listed first below. Save to refresh the list.'],
            'short_description' => ['type' => 'textarea', 'label' => 'Intro (under the product name)', 'rows' => 3],
            'image_path'        => ['type' => 'image', 'label' => 'Main pack shot', 'folder' => 'products',
                                    'help' => 'PNG with a transparent background works best. Empty = generated pack graphic.'],
            'meta_title'        => ['type' => 'text', 'label' => 'SEO title', 'help' => 'Empty = product name'],
            'meta_description'  => ['type' => 'textarea', 'label' => 'SEO description', 'rows' => 2],
            'sort_order'        => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'         => ['type' => 'checkbox', 'label' => 'Visible on the site'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'id'                => $record ? ['nullable'] : ['required', 'integer', 'min:1', 'unique:products,id'],
            'name'              => ['required', 'string', 'max:190'],
            'slug'              => ['required', 'string', 'max:190', 'regex:/^[a-z0-9-]+$/', Rule::unique('products', 'slug')->ignore($record?->getKey())],
            'attribute_set_id'  => ['nullable', 'exists:attribute_sets,id'],
            'short_description' => ['nullable', 'string', 'max:2000'],
            'image_path'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
            'meta_title'        => ['nullable', 'string', 'max:190'],
            'meta_description'  => ['nullable', 'string', 'max:300'],
            'sort_order'        => ['nullable', 'integer', 'min:0'],
            'is_active'         => ['nullable'],
        ];
    }

    /** Stay on the language that was being edited. */
    public function update(Request $request, $id)
    {
        $response = parent::update($request, $id);
        $lang     = Locale::where('url_prefix', $request->input('attr_lang'))->where('is_default', false)->value('url_prefix');

        return $lang ? $response->setTargetUrl($response->getTargetUrl().'?lang='.$lang.'#attributes') : $response;
    }

    protected function extraRules(?Model $record = null): array
    {
        return [
            'attributes'        => ['nullable', 'array'],
            'categories'        => ['nullable', 'array'],
            'categories.*'      => ['nullable', 'integer', 'exists:categories,id'],
            'gallery'           => ['nullable', 'array'],
            'gallery.*'         => ['image', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
            'gallery_alt'       => ['nullable', 'array'],
            'gallery_order'     => ['nullable', 'array'],
            'gallery_remove'    => ['nullable', 'array'],
        ];
    }

    protected function prepare(array $data, ?Model $record = null): array
    {
        $data = parent::prepare($data, $record);

        // The id is immutable once the product exists (it is the public URL ref).
        if ($record) {
            unset($data['id']);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    /** Attribute types that hold language-dependent text. */
    public const TEXT_TYPES = ['text', 'textarea', 'richtext', 'list'];

    protected function formData(Model $record): array
    {
        $inSet = $record->attributeSet?->productAttributes()->pluck('attributes.id')->all() ?? [];

        // ?lang=km edits the Khmer values of text attributes; '' = values for every language.
        $attrLocale = Locale::where('url_prefix', request('lang'))->where('is_default', false)->first();
        $localeCode = $attrLocale?->code ?? '';

        $values = $record->exists
            ? ProductAttributeValue::where('product_id', $record->id)->where('locale', $localeCode)->pluck('value', 'attribute_id')
            : collect();

        $defaults = $record->exists && $localeCode !== ''
            ? ProductAttributeValue::where('product_id', $record->id)->where('locale', '')->pluck('value', 'attribute_id')
            : collect();

        return [
            'groups'      => AttributeGroup::with('productAttributes')->orderBy('sort_order')->get(),
            'inSet'       => $inSet,
            'values'      => $values,
            'defaults'    => $defaults,
            'attrLocale'  => $attrLocale,
            'attrLocales' => Locale::orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->get(),
            'selected'   => $record->exists ? $record->categories()->pluck('categories.id')->all() : [],
            'images'     => $record->exists ? $record->images : collect(),
        ];
    }

    protected function afterSave(Model $record, Request $request): void
    {
        /** @var Product $record */
        $localeCode = Locale::where('code', $request->input('attr_locale'))->where('is_default', false)->value('code') ?? '';
        $this->saveAttributes($record, (array) $request->input('attributes', []), $localeCode);

        if ($request->has('categories')) {
            $ids = array_filter((array) $request->input('categories'));
            $record->categories()->sync(collect($ids)->mapWithKeys(fn ($id) => [(int) $id => ['sort_order' => $record->sort_order]])->all());
        }

        $this->saveGallery($record, $request);
    }

    private function saveAttributes(Product $product, array $input, string $locale = ''): void
    {
        $definitions = ProductAttribute::all()->keyBy('code');

        foreach ($input as $code => $raw) {
            $definition = $definitions[$code] ?? null;

            // A language-specific save only touches text attributes.
            if (! $definition || ($locale !== '' && ! in_array($definition->type, self::TEXT_TYPES, true))) {
                continue;
            }

            if ($definition->type === 'multiselect') {
                $raw = array_values(array_filter((array) $raw, 'strlen'));
            }

            $value = $definition->serialize($raw);
            $match = ['product_id' => $product->id, 'attribute_id' => $definition->id, 'locale' => $locale];

            if ($value === null || ($definition->type === 'boolean' && $value === '0')) {
                ProductAttributeValue::where($match)->delete();
            } else {
                ProductAttributeValue::updateOrCreate($match, ['value' => $value]);
            }
        }
    }

    private function saveGallery(Product $product, Request $request): void
    {
        $remove = array_map('intval', (array) $request->input('gallery_remove', []));

        foreach ($product->images as $image) {
            if (in_array($image->id, $remove, true)) {
                $file = public_path('uploads/'.$image->path);
                if (is_file($file)) {
                    @unlink($file);
                }
                $image->delete();
                continue;
            }

            $image->update([
                'alt'        => $request->input('gallery_alt.'.$image->id, $image->alt),
                'sort_order' => (int) $request->input('gallery_order.'.$image->id, $image->sort_order),
            ]);
        }

        $next = (int) $product->images()->max('sort_order');

        foreach ((array) $request->file('gallery', []) as $file) {
            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $this->storeUpload($file, 'products'),
                'sort_order' => $next += 10,
            ]);
        }
    }
}
