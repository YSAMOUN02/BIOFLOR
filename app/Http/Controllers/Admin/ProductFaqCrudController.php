<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductFaq;
use Illuminate\Database\Eloquent\Model;

class ProductFaqCrudController extends CrudController
{
    protected string $model      = ProductFaq::class;
    protected string $routeName  = 'admin.product-faqs';
    protected string $title      = 'Product Q&A';
    protected string $singular   = 'Product question';
    protected string $orderBy    = 'product_id';
    protected array  $searchable = ['question', 'answer'];

    protected function columns(): array
    {
        return [
            'product_label' => 'Product',
            'question'      => 'Question',
            'sort_order'    => 'Order',
        ];
    }

    protected function fields(): array
    {
        return [
            'product_id' => ['type' => 'select', 'label' => 'Product', 'options' => $this->productOptions()],
            'question'   => ['type' => 'text', 'label' => 'Question'],
            'answer'     => ['type' => 'richtext', 'label' => 'Answer (HTML)', 'rows' => 6],
            'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
        ];
    }

    private function productOptions(): array
    {
        return Product::active()->get()
            ->mapWithKeys(fn ($p) => [$p->id => html_entity_decode(strip_tags($p->name), ENT_QUOTES | ENT_HTML5, 'UTF-8')])
            ->all();
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'question'   => ['required', 'string', 'max:255'],
            'answer'     => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
