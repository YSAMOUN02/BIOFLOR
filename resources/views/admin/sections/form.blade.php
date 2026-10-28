@extends('admin.layout')

@section('title', $section->exists ? 'Edit section' : 'New section')

@section('content')
    @php
        $payload  = old('payload', json_encode($section->payload ?: new stdClass, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $values   = old('fields', $section->payload ?? []);
        $editor   = old('editor', $schema ? 'form' : 'json');
        $pageKeys = \App\Http\Controllers\Admin\SectionController::PAGES;
        $preview  = match ($section->page_key) {
            'home' => route('home'), 'our-products' => route('products'), 'faq' => route('faq'),
            'pdp'  => optional(\App\Models\Product::active()->first())->url ?? route('products'),
            default => route('page', ['slug' => $section->page_key ?: 'home']),
        };
    @endphp

    <form method="post" enctype="multipart/form-data"
          action="{{ $section->exists ? route('admin.sections.update', $section) : route('admin.sections.store') }}"
          class="stack js-section-form">
        @csrf
        @if ($section->exists) @method('PUT') @endif
        <input type="hidden" name="editor" value="{{ $editor }}" class="js-editor-mode">

        <div class="card">
            <div class="card__head">
                <h2>{{ $section->exists ? 'Edit section' : 'New section' }}</h2>
                <span class="pill pill--off">{{ $types[$section->type] ?? $section->type }}</span>
                <span class="card__spacer"></span>
                <a class="btn btn--ghost btn--sm" href="{{ $preview }}" target="_blank" rel="noopener">Preview page &rarr;</a>
                <a class="btn btn--ghost btn--sm" href="{{ route('admin.sections.index', ['page_key' => $section->page_key]) }}">Back</a>
            </div>

            <div class="card__body form-grid form-grid--2">
                <div class="field">
                    <label for="type">Component type</label>
                    <select id="type" name="type" class="js-section-type" data-create-url="{{ $section->exists ? '' : route('admin.sections.create', ['page_key' => $section->page_key]) }}">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $section->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @if ($section->exists)
                        <p class="help">Changing the type and saving starts the new type from its example content.</p>
                    @endif
                    @error('type') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="page_key">Page</label>
                    <input type="text" id="page_key" name="page_key" list="page-keys" value="{{ old('page_key', $section->page_key ?: 'home') }}">
                    <datalist id="page-keys">
                        @foreach ($pageKeys as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </datalist>
                    <p class="help">A new key creates a new page at /en/{key}.html.</p>
                    @error('page_key') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="region">Region</label>
                    <input type="text" id="region" name="region" list="regions" value="{{ old('region', $section->region ?: 'main') }}">
                    <datalist id="regions"><option value="main"><option value="after_main"><option value="middle"><option value="bottom"></datalist>
                    <p class="help">"main" everywhere; product pages use after_main, middle and bottom.</p>
                </div>

                <div class="field">
                    <label for="sort_order">Sort order</label>
                    <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $section->sort_order ?? 10) }}">
                    @error('sort_order') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="field field--check">
                    <input type="hidden" name="is_visible" value="0">
                    <input type="checkbox" id="is_visible" name="is_visible" value="1" @checked(old('is_visible', $section->is_visible ?? true))>
                    <label for="is_visible">Visible on the site</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card__head">
                <h2>Content</h2>
                @if ($section->exists && $schema && $locales->count() > 1)
                    <div class="segmented" aria-label="Language">
                        @foreach ($locales as $locale)
                            <a class="segmented__btn" aria-selected="{{ ($lang?->id ?? ($locale->is_default ? $locale->id : null)) === $locale->id ? 'true' : 'false' }}"
                               href="{{ route('admin.sections.edit', $section) }}{{ $locale->is_default ? '' : '?lang='.$locale->url_prefix }}">{{ $locale->label }}</a>
                        @endforeach
                    </div>
                @endif
                <span class="card__spacer"></span>
                @unless ($lang)
                    <div class="segmented" role="tablist">
                        @if ($schema)
                            <button type="button" class="segmented__btn js-editor-tab" data-mode="form" aria-selected="{{ $editor === 'form' ? 'true' : 'false' }}">Fields</button>
                        @endif
                        <button type="button" class="segmented__btn js-editor-tab" data-mode="json" aria-selected="{{ $editor === 'json' ? 'true' : 'false' }}">JSON (advanced)</button>
                    </div>
                @endunless
            </div>

            <div class="card__body">
                @error('uploads') <p class="error">{{ $message }}</p> @enderror

                @if ($lang)
                    <input type="hidden" name="lang" value="{{ $lang->url_prefix }}">
                    <p class="alert alert--info" style="margin:0 0 1rem">
                        Editing the <strong>{{ $lang->label }}</strong> text of this section. Empty fields show the English text.
                        Images, links and settings are shared — change them in English.
                    </p>
                    <div class="form-grid" lang="{{ $lang->url_prefix }}">
                        @include('admin.sections.fields', [
                            'schema'       => $schema,
                            'prefix'       => 'fields',
                            'uploadPrefix' => 'uploads',
                            'values'       => (array) (($section->translations ?? [])[$lang->url_prefix] ?? []),
                            'translating'  => true,
                            'fallbacks'    => (array) $section->payload,
                        ])
                    </div>
                @elseif ($schema)
                    <div class="js-editor-panel" data-mode="form" @if ($editor !== 'form') hidden @endif>
                        <p class="help" style="margin-bottom:1rem">
                            Text may use <code>{brand}</code>@if ($section->page_key === 'pdp') and <code>{product}</code>@endif.
                            Images and videos: upload a file, or type to pick one from the <a href="{{ route('admin.media.index') }}" target="_blank">media library</a>.
                        </p>
                        <div class="form-grid">
                            @include('admin.sections.fields', ['schema' => $schema, 'prefix' => 'fields', 'uploadPrefix' => 'uploads', 'values' => (array) $values])
                        </div>
                    </div>
                @endif

                @unless ($lang)
                <div class="js-editor-panel" data-mode="json" @if ($editor !== 'json') hidden @endif>
                    <div class="field">
                        <label for="payload">Content (JSON)</label>
                        <textarea id="payload" name="payload" rows="22" class="code">{{ $payload }}</textarea>
                        <p class="help">Used only when this tab is selected while saving.</p>
                        @error('payload') <p class="error">{{ $message }}</p> @enderror
                    </div>
                    <details class="field">
                        <summary>Example content for each type</summary>
                        @foreach ($examples as $exampleType => $example)
                            <p class="help" style="margin-top:1rem"><strong>{{ $types[$exampleType] ?? $exampleType }}</strong> <code>{{ $exampleType }}</code></p>
                            <pre class="log">{{ json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                        @endforeach
                    </details>
                </div>
                @endunless
            </div>
        </div>

        <datalist id="media-image">
            @foreach ($images as $media)<option value="{{ $media->path }}">{{ $media->title }}</option>@endforeach
        </datalist>
        <datalist id="media-video">
            @foreach ($videos as $media)<option value="{{ $media->path }}">{{ $media->title }}</option>@endforeach
        </datalist>

        <div class="form-actions sticky-actions">
            <button class="btn btn--primary" type="submit">{{ $section->exists ? 'Save section' : 'Create section' }}</button>
            <a class="btn btn--ghost" href="{{ route('admin.sections.index', ['page_key' => $section->page_key]) }}">Cancel</a>
        </div>
    </form>
@endsection
