<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeSet;
use App\Models\ProductAttribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/** Product types: which attributes a kind of product is edited with. */
class AttributeSetCrudController extends CrudController
{
    protected string $model     = AttributeSet::class;
    protected string $routeName = 'admin.attribute-sets';
    protected string $title     = 'Product types';
    protected string $singular  = 'Product type';
    protected string $orderBy   = 'name';

    protected function columns(): array
    {
        return ['code' => 'Code', 'name' => 'Name', 'description' => 'Description'];
    }

    protected function fields(): array
    {
        $record = $this->current;

        return [
            'code'          => ['type' => 'text', 'label' => 'Code', 'readonly_on_edit' => true],
            'name'          => ['type' => 'text', 'label' => 'Name'],
            'description'   => ['type' => 'textarea', 'label' => 'Description', 'rows' => 2],
            'attribute_ids' => ['type' => 'multicheck', 'label' => 'Attributes', 'virtual' => true,
                                'options' => ProductAttribute::with('group')->orderBy('sort_order')->get()
                                    ->mapWithKeys(fn ($a) => [$a->id => e($a->name).' <small>('.e($a->group->name ?? 'no group').')</small>'])->all(),
                                'value' => $record && $record->exists ? $record->productAttributes()->pluck('attributes.id')->all() : []],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'code'        => $record ? ['nullable'] : ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_-]*$/', 'unique:attribute_sets,code'],
            'name'        => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function extraRules(?Model $record = null): array
    {
        return ['attribute_ids' => ['nullable', 'array'], 'attribute_ids.*' => ['nullable', 'integer']];
    }

    protected function prepare(array $data, ?Model $record = null): array
    {
        $data = parent::prepare($data, $record);
        if ($record) {
            unset($data['code']);
        }

        return $data;
    }

    protected function afterSave(Model $record, Request $request): void
    {
        /** @var AttributeSet $record */
        $order = ProductAttribute::orderBy('sort_order')->pluck('sort_order', 'id');
        $ids   = array_filter((array) $request->input('attribute_ids', []));

        $record->productAttributes()->sync(collect($ids)->mapWithKeys(fn ($id) => [(int) $id => ['sort_order' => $order[$id] ?? 0]])->all());
    }
}
