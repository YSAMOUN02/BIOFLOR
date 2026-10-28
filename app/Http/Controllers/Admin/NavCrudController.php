<?php

namespace App\Http\Controllers\Admin;

use App\Models\NavItem;
use Illuminate\Database\Eloquent\Model;

class NavCrudController extends CrudController
{
    protected string $model      = NavItem::class;
    protected string $routeName  = 'admin.nav';
    protected string $title      = 'Navigation';
    protected string $singular   = 'Menu item';
    protected string $orderBy    = 'sort_order';
    protected array  $searchable = ['label', 'url'];

    protected function columns(): array
    {
        return ['label' => 'Label', 'url' => 'URL', 'parent_label' => 'Parent', 'sort_order' => 'Order'];
    }

    protected function fields(): array
    {
        return [
            'label'       => ['type' => 'text', 'label' => 'Label'],
            'url'         => ['type' => 'text', 'label' => 'URL', 'help' => 'Page address without the language, e.g. gut-health.html or our-products. It opens in the visitor\'s language.'],
            'parent_id'   => ['type' => 'select', 'label' => 'Parent', 'options' => $this->parentOptions(), 'help' => 'Items with children open a dropdown.'],
            'description' => ['type' => 'textarea', 'label' => 'Mobile menu intro', 'rows' => 2, 'help' => 'Top-level items with a dropdown: text above the links in the mobile menu. {brand} is replaced.'],
            'cta_label'   => ['type' => 'text', 'label' => 'Mobile menu button label', 'help' => 'e.g. Discover our range'],
            'sort_order'  => ['type' => 'number', 'label' => 'Sort order'],
        ];
    }

    private function parentOptions(): array
    {
        return ['' => '— Top level —'] + NavItem::roots()->pluck('label', 'id')->all();
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'label'        => ['required', 'string', 'max:120'],
            'url'          => ['nullable', 'string', 'max:255'],
            'parent_id'    => ['nullable', 'integer', 'exists:nav_items,id'],
            'description'  => ['nullable', 'string', 'max:500'],
            'cta_label'    => ['nullable', 'string', 'max:120'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepare(array $data, ?Model $record = null): array
    {
        $data = parent::prepare($data, $record);
        $data['parent_id'] = $data['parent_id'] ?: null;

        return $data;
    }
}
