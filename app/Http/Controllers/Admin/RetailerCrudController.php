<?php

namespace App\Http\Controllers\Admin;

use App\Models\Retailer;
use Illuminate\Database\Eloquent\Model;

class RetailerCrudController extends CrudController
{
    protected string $model      = Retailer::class;
    protected string $routeName  = 'admin.retailers';
    protected string $title      = 'Where to buy';
    protected string $singular   = 'Retailer';
    protected string $orderBy    = 'country';
    protected array  $searchable = ['name', 'country', 'city'];

    protected function columns(): array
    {
        return ['name' => 'Name', 'country' => 'Country', 'city' => 'City', 'is_online' => 'Online'];
    }

    protected function fields(): array
    {
        return [
            'name'      => ['type' => 'text', 'label' => 'Name'],
            'country'   => ['type' => 'text', 'label' => 'Country', 'help' => 'e.g. Cambodia'],
            'city'      => ['type' => 'text', 'label' => 'City'],
            'address'   => ['type' => 'text', 'label' => 'Address'],
            'phone'     => ['type' => 'text', 'label' => 'Phone'],
            'url'       => ['type' => 'text', 'label' => 'Website'],
            'is_online' => ['type' => 'checkbox', 'label' => 'Online shop', 'help' => 'Ticked: listed under "Buy online" (give its website). Not ticked: listed under "Local pharmacies" (give the address; the site adds a map link).'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'name'      => ['required', 'string', 'max:190'],
            'country'   => ['required', 'string', 'max:90'],
            'city'      => ['nullable', 'string', 'max:90'],
            'address'   => ['nullable', 'string', 'max:190'],
            'phone'     => ['nullable', 'string', 'max:60'],
            'url'       => ['nullable', 'url', 'max:190'],
            'is_online' => ['nullable'],
        ];
    }
}
