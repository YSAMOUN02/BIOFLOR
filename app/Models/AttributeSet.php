<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product type: the attributes a product of this kind is edited with
 * ("Probiotic medicine", "Food supplement", ...). Products without a set are
 * edited with every attribute.
 */
class AttributeSet extends Model
{
    protected $guarded = [];

    public function productAttributes(): BelongsToMany
    {
        return $this->belongsToMany(ProductAttribute::class, 'attribute_attribute_set', 'attribute_set_id', 'attribute_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
