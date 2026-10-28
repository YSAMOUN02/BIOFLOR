<?php

namespace App\Http\Controllers\Admin;

use App\Models\AttributeGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class AttributeGroupCrudController extends CrudController
{
    protected string $model     = AttributeGroup::class;
    protected string $routeName = 'admin.attribute-groups';
    protected string $title     = 'Attribute groups';
    protected string $singular  = 'Attribute group';
    protected string $orderBy   = 'sort_order';

    protected function columns(): array
    {
        return ['code' => 'Code', 'name' => 'Name', 'display' => 'Display', 'sort_order' => 'Order'];
    }

    protected function fields(): array
    {
        return [
            'code'       => ['type' => 'text', 'label' => 'Code', 'readonly_on_edit' => true],
            'name'       => ['type' => 'text', 'label' => 'Name'],
            'display'    => ['type' => 'select', 'label' => 'Shown as', 'options' => AttributeGroup::DISPLAYS],
            'sort_order' => ['type' => 'number', 'label' => 'Sort order', 'help' => 'Order of groups on the product page.'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'code'       => $record ? ['nullable'] : ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_-]*$/', 'unique:attribute_groups,code'],
            'name'       => ['required', 'string', 'max:120'],
            'display'    => ['required', Rule::in(array_keys(AttributeGroup::DISPLAYS))],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
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
}
