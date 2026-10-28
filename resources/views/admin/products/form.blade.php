@extends('admin.layout')

@section('title', $title)

@section('content')
    @php
        $isEdit = $record->exists;
        // Attributes of the product type first, the rest folded away.
        $splitGroups = $groups->map(fn ($group) => [
            'group' => $group,
            'main'  => $group->productAttributes->filter(fn ($a) => ! $inSet || in_array($a->id, $inSet, true)),
            'other' => $group->productAttributes->filter(fn ($a) => $inSet && ! in_array($a->id, $inSet, true)),
        ]);
        $displayNames = \App\Models\AttributeGroup::DISPLAYS;
    @endphp

    <form method="post" action="{{ $isEdit ? route($routeName.'.update', $record->getKey()) : route($routeName.'.store') }}"
          enctype="multipart/form-data" class="stack">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <div class="card">
            <div class="card__head">
                <h2>{{ $title }}</h2>
                <span class="card__spacer"></span>
                @if ($isEdit)
                    <a class="btn btn--ghost btn--sm" href="{{ $record->url }}" target="_blank" rel="noopener">View on site &rarr;</a>
                @endif
                <a class="btn btn--ghost btn--sm" href="{{ route($routeName.'.index') }}">Back to list</a>
            </div>
            <div class="card__body form-grid">
                @foreach ($fields as $name => $field)
                    @include('admin.crud.field', ['name' => $name, 'field' => $field, 'record' => $record])
                @endforeach
            </div>
        </div>

        @include('admin.crud.translations')

        <div class="card">
            <div class="card__head"><h2>Categories</h2><span class="help">Each format category becomes a tab on the range pages.</span></div>
            <div class="card__body">
                <input type="hidden" name="categories[]" value="">
                <div class="checks">
                    @foreach ($categories->whereNull('parent_id') as $root)
                        <label class="checks__item"><input type="checkbox" name="categories[]" value="{{ $root->id }}" @checked(in_array($root->id, old('categories', $selected)))> <strong>{{ $root->name }}</strong></label>
                        @foreach ($categories->where('parent_id', $root->id) as $child)
                            <label class="checks__item checks__item--child"><input type="checkbox" name="categories[]" value="{{ $child->id }}" @checked(in_array($child->id, old('categories', $selected)))> {{ $child->name }}</label>
                        @endforeach
                    @endforeach
                </div>
                <p class="help"><a href="{{ route('admin.categories.index') }}">Manage categories</a></p>
            </div>
        </div>

        @php
            $textTypes = \App\Http\Controllers\Admin\ProductCrudController::TEXT_TYPES;
            if ($attrLocale) {
                // Language mode: only text attributes have per-language values.
                $splitGroups = $splitGroups->map(fn ($row) => [
                    'group' => $row['group'],
                    'main'  => $row['main']->filter(fn ($a) => in_array($a->type, $textTypes, true)),
                    'other' => $row['other']->filter(fn ($a) => in_array($a->type, $textTypes, true)),
                ]);
            }
        @endphp
        <div class="card" id="attributes">
            <div class="card__head">
                <h2>Attributes</h2>
                @if ($isEdit)
                    <div class="segmented" aria-label="Language of the attribute values">
                        @foreach ($attrLocales as $locale)
                            <a class="segmented__btn" aria-selected="{{ ($attrLocale?->id ?? ($locale->is_default ? $locale->id : null)) === $locale->id ? 'true' : 'false' }}"
                               href="{{ route($routeName.'.edit', $record->getKey()) }}{{ $locale->is_default ? '' : '?lang='.$locale->url_prefix }}#attributes">{{ $locale->label }}</a>
                        @endforeach
                    </div>
                @endif
                <span class="card__spacer"></span>
                <a class="btn btn--ghost btn--sm" href="{{ route('admin.attributes.index') }}">Manage attributes</a>
            </div>
            <div class="card__body stack">
                @if ($attrLocale)
                    <input type="hidden" name="attr_locale" value="{{ $attrLocale->code }}">
                    <input type="hidden" name="attr_lang" value="{{ $attrLocale->url_prefix }}">
                    <p class="alert alert--info" style="margin:0">
                        Editing the <strong>{{ $attrLocale->label }}</strong> text of this product's attributes. Empty fields show the English value;
                        numbers, yes/no badges and choices are the same in every language.
                    </p>
                @endif
                @foreach ($splitGroups as $row)
                    @continue($row['main']->isEmpty() && $row['other']->isEmpty())
                    <fieldset class="attr-group">
                        <legend>{{ $row['group']->name }} <span class="pill pill--off">{{ \Illuminate\Support\Str::before($displayNames[$row['group']->display] ?? $row['group']->display, ' —') }}</span></legend>
                        <div class="form-grid">
                            @foreach ($row['main'] as $attribute)
                                @include('admin.products.attribute-input', ['attribute' => $attribute, 'stored' => $values[$attribute->id] ?? null, 'fallback' => $attrLocale ? ($defaults[$attribute->id] ?? null) : null])
                            @endforeach
                        </div>
                        @if ($row['other']->isNotEmpty())
                            <details class="attr-other">
                                <summary>{{ $row['other']->count() }} more not in this product type</summary>
                                <div class="form-grid">
                                    @foreach ($row['other'] as $attribute)
                                        @include('admin.products.attribute-input', ['attribute' => $attribute, 'stored' => $values[$attribute->id] ?? null, 'fallback' => $attrLocale ? ($defaults[$attribute->id] ?? null) : null])
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </fieldset>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card__head"><h2>Gallery</h2><span class="help">Extra images shown in the product page carousel, after the main pack shot.</span></div>
            <div class="card__body stack">
                @if ($images->isNotEmpty())
                    <div class="gallery-admin">
                        @foreach ($images as $image)
                            <div class="gallery-admin__item">
                                <img src="{{ $image->url }}" alt="">
                                <input type="text" name="gallery_alt[{{ $image->id }}]" value="{{ $image->alt }}" placeholder="Alt text">
                                <input type="number" name="gallery_order[{{ $image->id }}]" value="{{ $image->sort_order }}" title="Order">
                                <label><input type="checkbox" name="gallery_remove[]" value="{{ $image->id }}"> Remove</label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="field">
                    <label for="gallery">Add images</label>
                    <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple>
                    @error('gallery.*') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Create product' }}</button>
            <a class="btn btn--ghost" href="{{ route($routeName.'.index') }}">Cancel</a>
        </div>
    </form>
@endsection
