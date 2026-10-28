<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Groups attributes and decides where they render on the product page.
 * The `display` slot is what the templates look for, so new attributes show
 * up on the site as soon as they are put in a displayed group.
 */
class AttributeGroup extends Model
{
    public const DISPLAYS = [
        'labels'    => 'Labels — tile footer and under the product name',
        'badges'    => 'Icon badges — yes/no attributes shown with an icon',
        'specs'     => 'Key facts — name/value list',
        'accordion' => 'Accordion — one panel per attribute',
        'steps'     => 'How to use — numbered steps',
        'hidden'    => 'Not shown on the site',
    ];

    protected $guarded = [];

    public function productAttributes(): HasMany
    {
        return $this->hasMany(ProductAttribute::class)->orderBy('sort_order');
    }
}
