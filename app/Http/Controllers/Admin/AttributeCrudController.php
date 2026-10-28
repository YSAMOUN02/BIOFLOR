<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeGroup;
use App\Models\AttributeSet;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Attribute dictionary: what can be recorded about a product, and where it shows. */
class AttributeCrudController extends CrudController
{
    protected string $model      = ProductAttribute::class;
    protected string $routeName  = 'admin.attributes';
    protected string $title      = 'Attributes';
    protected string $singular   = 'Attribute';
    protected string $orderBy    = 'sort_order';
    protected array  $searchable = ['code', 'name'];

    protected function columns(): array
    {
        return ['code' => 'Code', 'name' => 'Name', 'type' => 'Type', 'group_name' => 'Group', 'sort_order' => 'Order'];
    }

    protected function fields(): array
    {
        $record = $this->current;

        return [
            'code'               => ['type' => 'text', 'label' => 'Code', 'help' => 'Lowercase, used by templates and the importer (e.g. pack_size). Cannot change later.', 'readonly_on_edit' => true],
            'name'               => ['type' => 'text', 'label' => 'Name', 'help' => 'Shown on the site (accordion title, badge label, key fact name).'],
            'attribute_group_id' => ['type' => 'select', 'label' => 'Group (where it shows)', 'options' => ['' => '— None —'] + AttributeGroup::orderBy('sort_order')->get()
                ->mapWithKeys(fn ($g) => [$g->id => $g->name.' — '.\Illuminate\Support\Str::before(AttributeGroup::DISPLAYS[$g->display] ?? $g->display, ' —')])->all()],
            'type'               => ['type' => 'select', 'label' => 'Type', 'options' => ProductAttribute::TYPES],
            'unit'               => ['type' => 'text', 'label' => 'Unit', 'help' => 'Optional, e.g. mg, ml, g.'],
            'options'            => ['type' => 'keyvalue', 'label' => 'Choices (single / multiple choice types)', 'rows' => 5, 'help' => 'One per line: value = Label. Example: capsule = Capsule'],
            'icon'               => ['type' => 'select', 'label' => 'Badge icon', 'options' => [
                '' => '— None —', 'gmo-free' => 'No GMO', 'gluten-free' => 'Gluten free', 'vegetarian' => 'Vegetarian',
                'lactose-free' => 'Lactose free', 'check' => 'Check mark',
            ], 'help' => 'Used when the attribute is in a Badges group.'],
            'help'               => ['type' => 'text', 'label' => 'Help text for editors'],
            'is_required'        => ['type' => 'checkbox', 'label' => 'Required'],
            'is_filterable'      => ['type' => 'checkbox', 'label' => 'Filterable'],
            'is_searchable'      => ['type' => 'checkbox', 'label' => 'Searchable (site search looks in this value)'],
            'sort_order'         => ['type' => 'number', 'label' => 'Sort order'],
            'set_ids'            => ['type' => 'multicheck', 'label' => 'Product types using this attribute', 'virtual' => true,
                                     'options' => AttributeSet::orderBy('name')->pluck('name', 'id')->all(),
                                     'value' => $record && $record->exists ? $record->sets()->pluck('attribute_sets.id')->all() : []],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'code'               => $record ? ['nullable'] : ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:attributes,code'],
            'name'               => ['required', 'string', 'max:120'],
            'attribute_group_id' => ['nullable', 'exists:attribute_groups,id'],
            'type'               => ['required', Rule::in(array_keys(ProductAttribute::TYPES))],
            'unit'               => ['nullable', 'string', 'max:20'],
            'options'            => ['nullable', 'string', 'max:5000'],
            'icon'               => ['nullable', 'string', 'max:60'],
            'help'               => ['nullable', 'string', 'max:255'],
            'is_required'        => ['nullable'],
            'is_filterable'      => ['nullable'],
            'is_searchable'      => ['nullable'],
            'sort_order'         => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function extraRules(?Model $record = null): array
    {
        return ['set_ids' => ['nullable', 'array'], 'set_ids.*' => ['nullable', 'integer']];
    }

    protected function prepare(array $data, ?Model $record = null): array
    {
        $data = parent::prepare($data, $record);

        if ($record) {
            unset($data['code']);
        }

        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        /** @var ProductAttribute $record */
        $record->sets()->sync(array_filter((array) $request->input('set_ids', [])));
    }
}
