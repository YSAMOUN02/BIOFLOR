{{-- Schema-driven section fields. Expects $schema, $prefix (input name), $uploadPrefix, $values.
     Translation mode ($translating = true): only text fields, structure taken from $fallbacks (the
     default-language payload), which also supplies the English hints. --}}
@php
    $translating = $translating ?? false;
    $fallbacks   = $fallbacks ?? [];
@endphp
@foreach ($schema as $key => $def)
    @php
        $name     = $prefix.'['.$key.']';
        $upload   = $uploadPrefix.'['.$key.']';
        $value    = $values[$key] ?? null;
        $fallback = $fallbacks[$key] ?? null;
        $id       = 'f-'.substr(md5($name), 0, 10);
        $type     = $def['type'];
    @endphp

    @if ($translating)
        @if ($type === 'list')
            @if (! empty($fallback))
                <div class="repeater">
                    <span class="field__legend">{{ $def['label'] }}</span>
                    @foreach (array_values((array) $fallback) as $i => $baseItem)
                        <fieldset class="repeater__item">
                            <div class="repeater__bar"><span class="repeater__title">{{ $def['item'] ?? 'Item' }} {{ $i + 1 }}</span></div>
                            <div class="form-grid">
                                @include('admin.sections.fields', [
                                    'schema' => $def['fields'], 'prefix' => $name.'['.$i.']', 'uploadPrefix' => $upload.'['.$i.']',
                                    'values' => (array) (((array) $value)[$i] ?? []), 'translating' => true, 'fallbacks' => (array) $baseItem,
                                ])
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            @endif
        @elseif ($type === 'group')
            <fieldset class="attr-group">
                <legend>{{ $def['label'] }}</legend>
                <div class="form-grid">
                    @include('admin.sections.fields', ['schema' => $def['fields'], 'prefix' => $name, 'uploadPrefix' => $upload, 'values' => (array) $value, 'translating' => true, 'fallbacks' => (array) $fallback])
                </div>
            </fieldset>
        @elseif (in_array($type, \App\Http\Controllers\Admin\SectionController::TEXT_FIELDS, true) && filled($fallback))
            <div class="field">
                <label for="{{ $id }}">{{ $def['label'] }}</label>
                @if ($type === 'text')
                    <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">
                @else
                    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $def['rows'] ?? 3 }}" class="{{ $type === 'html' ? 'code' : '' }}">{{ $value }}</textarea>
                @endif
                <p class="help">English: {{ \Illuminate\Support\Str::limit(is_string($fallback) ? ($type === 'html' ? $fallback : plain_text($fallback)) : '', 220) }}</p>
            </div>
        @endif
        @continue
    @endif

    @if ($type === 'list')
        <div class="repeater js-repeater">
            <span class="field__legend">{{ $def['label'] }}</span>
            <div class="repeater__items js-repeater-items">
                @foreach (array_values((array) $value) as $i => $item)
                    @include('admin.sections.repeater-item', ['def' => $def, 'name' => $name, 'upload' => $upload, 'index' => $i, 'item' => (array) $item])
                @endforeach
            </div>
            <template class="js-repeater-template">
                @include('admin.sections.repeater-item', ['def' => $def, 'name' => $name, 'upload' => $upload, 'index' => '__INDEX__', 'item' => []])
            </template>
            <button class="btn btn--ghost btn--sm js-repeater-add" type="button">+ Add {{ strtolower($def['item'] ?? 'item') }}</button>
        </div>

    @elseif ($type === 'group')
        <fieldset class="attr-group">
            <legend>{{ $def['label'] }}</legend>
            <div class="form-grid">
                @include('admin.sections.fields', ['schema' => $def['fields'], 'prefix' => $name, 'uploadPrefix' => $upload, 'values' => (array) $value])
            </div>
        </fieldset>

    @elseif ($type === 'checkbox')
        <div class="field field--check">
            <input type="hidden" name="{{ $name }}" value="0">
            <input type="checkbox" id="{{ $id }}" name="{{ $name }}" value="1" @checked($value)>
            <label for="{{ $id }}">{{ $def['label'] }}</label>
        </div>

    @elseif ($type === 'image' || $type === 'video')
        @php
            $url     = media_url($value);
            $isVideo = $type === 'video';
            $isFile  = $url && preg_match('/\.(mp4|webm|ogg)(\?|#|$)/i', $url);
        @endphp
        <div class="field media-field js-media-field" data-kind="{{ $type }}">
            <label for="{{ $id }}">{{ $def['label'] }}</label>
            <div class="media-field__row">
                <div class="media-field__preview js-media-preview">
                    @if ($url && ! $isVideo)
                        <img src="{{ $url }}" alt="">
                    @elseif ($url && $isFile)
                        <video src="{{ $url }}#t=0.5" muted preload="metadata"></video>
                    @elseif ($url)
                        <span>Link</span>
                    @else
                        <span>None</span>
                    @endif
                </div>
                <div class="media-field__inputs">
                    <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" list="media-{{ $type }}"
                           placeholder="{{ $isVideo ? 'library/… .mp4 or a YouTube / Vimeo link' : 'library/… or img/…' }}" class="js-media-path">
                    <label class="media-field__upload">
                        <span>Upload {{ $isVideo ? 'a video' : 'an image' }}</span>
                        <input type="file" name="{{ $upload }}" accept="{{ $isVideo ? 'video/mp4,video/webm,video/ogg' : 'image/*' }}" class="js-media-file">
                    </label>
                    @if ($value)
                        <button type="button" class="btn btn--ghost btn--sm js-media-clear">Remove</button>
                    @endif
                </div>
            </div>
            @if (! empty($def['help']))<p class="help">{{ $def['help'] }}</p>@endif
        </div>

    @else
        <div class="field">
            <label for="{{ $id }}">{{ $def['label'] }}</label>

            @if ($type === 'select')
                <select id="{{ $id }}" name="{{ $name }}">
                    @foreach ($def['options'] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select>
            @elseif ($type === 'html' || $type === 'textarea')
                <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $def['rows'] ?? 3 }}" class="{{ $type === 'html' ? 'code' : '' }}">{{ $value }}</textarea>
                @if ($type === 'html')<p class="help">HTML allowed: &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;sup&gt;, &lt;ul&gt;&lt;li&gt;, links.</p>@endif
            @elseif ($type === 'tags')
                <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ implode(', ', (array) $value) }}">
            @elseif ($type === 'number')
                <input type="number" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" step="any">
            @else
                <input type="text" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}">
            @endif

            @if (! empty($def['help']))<p class="help">{{ $def['help'] }}</p>@endif
        </div>
    @endif
@endforeach
