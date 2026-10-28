<?php

namespace App\Http\Controllers\Admin;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Model;

class FaqCrudController extends CrudController
{
    protected string $model      = Faq::class;
    protected string $routeName  = 'admin.faqs';
    protected string $title      = 'Q&A';
    protected string $singular   = 'Question';
    protected string $orderBy    = 'sort_order';
    protected array  $searchable = ['question', 'answer'];

    protected function columns(): array
    {
        return ['question' => 'Question', 'group' => 'Group', 'sort_order' => 'Order'];
    }

    protected function fields(): array
    {
        return [
            'group'      => ['type' => 'text', 'label' => 'Group', 'help' => 'Questions are grouped by this value on the Q&A page'],
            'question'   => ['type' => 'text', 'label' => 'Question'],
            'answer'     => ['type' => 'richtext', 'label' => 'Answer (HTML)', 'rows' => 6],
            'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
        ];
    }

    protected function rules(?Model $record = null): array
    {
        return [
            'group'      => ['required', 'string', 'max:60'],
            'question'   => ['required', 'string', 'max:255'],
            'answer'     => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
